{
        $this->ocrResult->update(['status' => 'processing']);
        
        try {
            $imagePath = storage_path('app/' . $this->ocrResult->image_path);
            
            $text = (new TesseractOCR($imagePath))
                ->lang('eng+ind')
                ->run();

            // Parse the text (simplified version)
            $lines = explode("\n", $text);
            $data = [];
            
            foreach ($lines as $line) {
                if (trim($line)) {
                    $data[] = array_map('trim', preg_split('/\s{2,}|\t/', $line));
                }
            }
            
            $this->ocrResult->update([
                'detected_data' => $data,
                'status' => 'processed'
            ]);
            
        } catch (\Exception $e) {
            $this->ocrResult->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
=======
    public function handle()
    {
        $this->ocrResult->update(['status' => 'processing']);

        try {
            $imagePath = storage_path('app/' . $this->ocrResult->image_path);

            // Optimize image for OCR
            $optimizedPath = $this->optimizeImage($this->ocrResult->image_path);

            // Process OCR with enhanced settings
            $text = (new TesseractOCR($optimizedPath ?? $imagePath))
                ->lang('eng+ind')
                ->psm(6)
                ->oem(3)
                ->configFile(storage_path('app/tesseract/config.txt'))
                ->allowlist('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.,;:!?@#$%^&*()-_=+[]{}|\'"<>/\\ ')
                ->userPatterns(storage_path('app/tesseract/patterns.txt'))
                ->tessVariable('textord_tabfind_find_tables', '1')
                ->tessVariable('textord_tablefind_recognize_tables', '1')
                ->tessVariable('textord_tablefind_enable_rule_lines', '1')
                ->tessVariable('textord_tablefind_enable_line_finding', '1')
                ->run();

            // Parse the text with advanced table parsing
            $data = $this->parseTableFromText($text, $this->ocrResult->has_header);

            $this->ocrResult->update([
                'detected_data' => $data['rows'],
                'status' => 'processed',
                'row_count' => count($data['rows'])
            ]);

            // Clean up optimized image
            if ($optimizedPath && $optimizedPath !== $this->ocrResult->image_path && Storage::exists($optimizedPath)) {
                Storage::delete($optimizedPath);
            }

        } catch (\Exception $e) {
            $this->ocrResult->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }

    private function optimizeImage($imagePath)
    {
        $fullPath = storage_path('app/' . $imagePath);
        $optimizedPath = 'ocr_optimized/' . basename($imagePath);
        $optimizedFullPath = storage_path('app/' . $optimizedPath);

        // Ensure directory exists
        if (!file_exists(dirname($optimizedFullPath))) {
            mkdir(dirname($optimizedFullPath), 0755, true);
        }

        try {
            $manager = ImageManager::gd();
            $image = $manager->read($fullPath);

            // Resize if too large (max 2500px width for better table detection)
            if ($image->width() > 2500) {
                $image->resize(2500, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Convert to grayscale for better OCR
            $image->greyscale();

            // Enhance contrast for table lines and text
            $image->contrast(25);

            // Apply adaptive thresholding for better table structure
            $image->brightness(10);

            // Sharpen image for clearer text
            $image->sharpen(15);

            // Save as PNG for better quality
            $image->save($optimizedFullPath, 100, 'png');

            return $optimizedPath;

        } catch (\Exception $e) {
            // If optimization fails, return original path
            return $imagePath;
        }
    }

    private function parseTableFromText($text, $hasHeader)
    {
        $lines = explode("\n", trim($text));
        $data = ['columns' => [], 'rows' => []];

        if (empty($lines)) {
            return $data;
        }

        // Enhanced table parsing with multiple strategies
        $parsedRows = $this->advancedTableParsing($lines);

        // If no rows parsed, return empty data
        if (empty($parsedRows)) {
            return $data;
        }

        // Determine the maximum number of columns
        $maxColumns = max(array_map('count', $parsedRows));

        if ($hasHeader && !empty($parsedRows)) {
            // Use first row as headers
            $data['columns'] = $parsedRows[0];
            $data['rows'] = array_slice($parsedRows, 1);
        } else {
            // Generate column names
            for ($i = 1; $i <= $maxColumns; $i++) {
                $data['columns'][] = 'Kolom ' . $i;
            }
            $data['rows'] = $parsedRows;
        }

        // Clean column names
        $data['columns'] = array_map(function($column) {
            return $this->cleanColumnName($column);
        }, $data['columns']);

        // Ensure all rows have same number of columns
        foreach ($data['rows'] as &$row) {
            while (count($row) < count($data['columns'])) {
                $row[] = null;
            }
            $row = array_slice($row, 0, count($data['columns']));
        }

        return $data;
    }

    private function advancedTableParsing($lines)
    {
        $parsedRows = [];
        $currentRow = [];
        $inTable = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if line contains table-like structure
            if ($this->isTableLine($line)) {
                $inTable = true;

                // Try multiple parsing strategies
                $cells = $this->parseTableLine($line);

                if (!empty($cells)) {
                    // If we have a current row, merge or add
                    if (!empty($currentRow)) {
                        $currentRow = $this->mergeRows($currentRow, $cells);
                    } else {
                        $currentRow = $cells;
                    }
                }
            } else if ($inTable && !empty($currentRow)) {
                // End of table row, save current row
                $parsedRows[] = $currentRow;
                $currentRow = [];
                $inTable = false;
            }
        }

        // Add last row if exists
        if (!empty($currentRow)) {
            $parsedRows[] = $currentRow;
        }

        // If no structured parsing worked, fall back to simple parsing
        if (empty($parsedRows)) {
            $parsedRows = $this->fallbackTableParsing($lines);
        }

        return $parsedRows;
    }

    private function isTableLine($line)
    {
        // Check for table indicators: multiple spaces, pipes, semicolons, etc.
        return preg_match('/(\s{2,}|\t|\||;|\s*,\s*(?=\S))/', $line) ||
               preg_match('/\d+\s*[.,]\s*\d+/', $line) || // Numbers with decimals
               preg_match('/[A-Za-z]\s+[A-Za-z]/', $line); // Multiple words
    }

    private function parseTableLine($line)
    {
        $cells = [];

        // Strategy 1: Split by table borders (|)
        if (strpos($line, '|') !== false) {
            $cells = array_map('trim', explode('|', $line));
            $cells = array_filter($cells, function($cell) {
                return !empty($cell) && strlen($cell) < 500;
            });
        }

        // Strategy 2: Split by multiple spaces or tabs
        if (empty($cells)) {
            $cells = preg_split('/\s{2,}|\t/', $line);
            $cells = array_map('trim', $cells);
            $cells = array_filter($cells, function($cell) {
                return !empty($cell) && strlen($cell) < 500;
            });
        }

        // Strategy 3: Split by semicolons
        if (empty($cells)) {
            $cells = array_map('trim', explode(';', $line));
            $cells = array_filter($cells, function($cell) {
                return !empty($cell) && strlen($cell) < 500;
            });
        }

        // Strategy 4: Intelligent comma splitting (avoid splitting numbers)
        if (empty($cells)) {
            $cells = $this->intelligentCommaSplit($line);
        }

        return array_values($cells);
    }

    private function intelligentCommaSplit($line)
    {
        $cells = [];
        $current = '';
        $inQuotes = false;

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];

            if ($char === '"') {
                $inQuotes = !$inQuotes;
            } elseif ($char === ',' && !$inQuotes) {
                // Check if this comma is between numbers (decimal separator)
                if (preg_match('/\d$/', $current) && preg_match('/^\d/', substr($line, $i + 1))) {
                    $current .= $char;
                } else {
                    $cells[] = trim($current);
                    $current = '';
                    continue;
                }
            }

            $current .= $char;
        }

        if (!empty($current)) {
            $cells[] = trim($current);
        }

        return array_filter($cells, function($cell) {
            return !empty($cell) && strlen($cell) < 500;
        });
    }

    private function mergeRows($row1, $row2)
    {
        // If rows have different lengths, try to merge intelligently
        if (count($row1) === count($row2)) {
            return $row1; // Keep the first one if same length
        }

        // If one row is longer, keep the longer one
        return count($row1) >= count($row2) ? $row1 : $row2;
    }

    private function fallbackTableParsing($lines)
    {
        $parsedRows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Simple fallback: split by multiple spaces
            $cells = preg_split('/\s{2,}|\t/', $line);
            $cells = array_map('trim', $cells);
            $cells = array_filter($cells, function($cell) {
                return !empty($cell) && strlen($cell) < 500;
            });

            if (!empty($cells)) {
                $parsedRows[] = array_values($cells);
            }
        }

        return $parsedRows;
    }

    private function cleanColumnName($column)
    {
        // Remove special characters but keep spaces and basic punctuation
        $column = preg_replace('/[^\p{L}\p{N}\s.,:;\-]/u', '', $column);
        $column = trim($column);

        // If empty after cleaning, generate a name
        if (empty($column)) {
            return 'Kolom';
        }

        // Limit length
        if (strlen($column) > 100) {
            $column = substr($column, 0, 100) . '...';
        }

        return $column;
    }
