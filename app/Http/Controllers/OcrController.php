<?php

namespace App\Http\Controllers;

use App\Models\OcrResult;
use App\Models\Dataset;
use App\Models\DatasetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class OcrController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Method untuk menampilkan halaman create OCR
    public function create()
    {
        $datasets = Dataset::where('user_id', auth()->id())->get();
        return view('ocr.create', compact('datasets'));
    }

    // Method untuk store/process OCR
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'dataset_name' => 'required_if:dataset_id,null|string|max:255',
            'has_header' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $image = $request->file('image');
        $originalName = $image->getClientOriginalName();
        $filename = time() . '_' . Str::random(10) . '_' . $originalName;
        $path = $image->storeAs('ocr_images', $filename);

        try {
            // Optimize image for OCR
            $optimizedPath = $this->optimizeImage($path);
            
            // Process OCR
            $ocrData = $this->processOcr($optimizedPath, $request->has_header);
            
            // Validate OCR results
            if (empty($ocrData['columns']) || empty($ocrData['rows'])) {
                throw new \Exception('Gagal mendeteksi tabel dari gambar. Pastikan gambar jelas dan mengandung tabel.');
            }

            // Create or get dataset
            $dataset = null;
            if ($request->dataset_id) {
                $dataset = Dataset::where('user_id', auth()->id())
                    ->findOrFail($request->dataset_id);
                
                // Add new columns if they don't exist
                $existingColumns = $dataset->columns ?? [];
                $newColumns = array_diff($ocrData['columns'], $existingColumns);
                
                if (!empty($newColumns)) {
                    $dataset->columns = array_merge($existingColumns, $newColumns);
                    $dataset->save();
                }
            } else {
                $dataset = Dataset::create([
                    'name' => $request->dataset_name,
                    'description' => 'Dibuat dari OCR: ' . $originalName . 
                                   ($request->has_header ? ' (dengan header)' : ' (tanpa header)'),
                    'columns' => $ocrData['columns'],
                    'user_id' => auth()->id(),
                    'row_count' => 0
                ]);
            }

            // Import data
            $importedCount = $this->importOcrData($dataset, $ocrData['rows']);

            // Create OCR result record
            $ocrResult = OcrResult::create([
                'image_path' => $path,
                'detected_data' => $ocrData['rows'],
                'status' => 'processed',
                'user_id' => auth()->id(),
                'dataset_id' => $dataset->id,
                'has_header' => $request->has_header,
                'row_count' => $importedCount
            ]);

            // Clean up optimized image
            if ($optimizedPath !== $path && Storage::exists($optimizedPath)) {
                Storage::delete($optimizedPath);
            }

            return redirect()->route('datasets.show', $dataset->id)
                ->with('success', 'OCR berhasil! ' . $importedCount . ' baris data ditambahkan.');

        } catch (\Exception $e) {
            // Clean up files if error occurs
            if (isset($filename) && Storage::exists('ocr_images/' . $filename)) {
                Storage::delete('ocr_images/' . $filename);
            }
            if (isset($optimizedPath) && $optimizedPath !== $path && Storage::exists($optimizedPath)) {
                Storage::delete($optimizedPath);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal memproses OCR: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Method untuk preview OCR
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            'has_header' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            $image = $request->file('image');
            $tempPath = $image->storeAs('temp_ocr', time() . '_' . $image->getClientOriginalName());
            
            // Optimize image
            $optimizedPath = $this->optimizeImage($tempPath);
            
            // Process OCR for preview
            $ocrData = $this->processOcr($optimizedPath, $request->has_header);
            
            // Clean up temporary files
            Storage::delete($tempPath);
            if ($optimizedPath !== $tempPath) {
                Storage::delete($optimizedPath);
            }

            if (empty($ocrData['columns']) || empty($ocrData['rows'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mendeteksi tabel dari gambar. Pastikan gambar jelas dan fokus.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'columns' => $ocrData['columns'],
                'total_rows' => count($ocrData['rows']),
                'preview' => array_slice($ocrData['rows'], 0, 5),
                'has_header' => $request->has_header
            ]);
            
        } catch (\Exception $e) {
            // Clean up temporary files
            if (isset($tempPath) && Storage::exists($tempPath)) {
                Storage::delete($tempPath);
            }
            if (isset($optimizedPath) && $optimizedPath !== $tempPath && Storage::exists($optimizedPath)) {
                Storage::delete($optimizedPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses OCR: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk menampilkan daftar OCR results
    public function index()
    {
        $results = OcrResult::where('user_id', auth()->id())
            ->with('dataset')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('ocr.index', compact('results'));
    }

    // Method untuk menampilkan detail OCR result
    public function show($id)
    {
        $result = OcrResult::where('user_id', auth()->id())
            ->with('dataset')
            ->findOrFail($id);
        
        return view('ocr.show', compact('result'));
    }

    // Method untuk retry OCR
    public function retry($id)
    {
        $result = OcrResult::where('user_id', auth()->id())->findOrFail($id);
        
        try {
            $result->update(['status' => 'processing']);
            
            // Process OCR again
            $optimizedPath = $this->optimizeImage($result->image_path);
            $ocrData = $this->processOcr($optimizedPath, $result->has_header);
            
            $result->update([
                'detected_data' => $ocrData['rows'],
                'status' => 'processed',
                'row_count' => count($ocrData['rows'])
            ]);
            
            // If dataset exists, update it
            if ($result->dataset) {
                $this->importOcrData($result->dataset, $ocrData['rows']);
            }
            
            // Clean up optimized image
            if ($optimizedPath !== $result->image_path && Storage::exists($optimizedPath)) {
                Storage::delete($optimizedPath);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'OCR berhasil diproses ulang'
            ]);
            
        } catch (\Exception $e) {
            $result->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses ulang OCR: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk save OCR data to dataset
    public function saveToDataset(Request $request, $id)
    {
        $result = OcrResult::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'dataset_name' => 'required|string|max:255',
            'columns' => 'required|array'
        ]);
        
        try {
            // Create new dataset
            $dataset = Dataset::create([
                'name' => $request->dataset_name,
                'description' => 'Dibuat dari OCR Result #' . $result->id,
                'columns' => $request->columns,
                'user_id' => auth()->id()
            ]);
            
            // Import data
            $importedCount = $this->importOcrData($dataset, $result->detected_data ?? []);
            
            // Update OCR result
            $result->update([
                'dataset_id' => $dataset->id,
                'row_count' => $importedCount
            ]);
            
            return redirect()->route('datasets.show', $dataset->id)
                ->with('success', 'Data berhasil disimpan ke dataset baru! ' . $importedCount . ' baris ditambahkan.');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    // Method untuk menghapus OCR result
    public function destroy($id)
    {
        $result = OcrResult::where('user_id', auth()->id())->findOrFail($id);
        
        // Delete image
        if (Storage::exists($result->image_path)) {
            Storage::delete($result->image_path);
        }
        
        $result->delete();
        
        return redirect()->route('ocr.index')
            ->with('success', 'OCR result berhasil dihapus');
    }

    // Method untuk check status OCR (API)
    public function checkStatus($id)
    {
        $result = OcrResult::where('user_id', auth()->id())->findOrFail($id);
        
        return response()->json([
            'status' => $result->status,
            'row_count' => $result->row_count,
            'error_message' => $result->error_message,
            'created_at' => $result->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $result->updated_at->format('Y-m-d H:i:s'),
        ]);
    }

    // Helper methods
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
            $image = Image::make($fullPath);
            
            // Resize if too large (max 2000px width)
            if ($image->width() > 2000) {
                $image->resize(2000, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Convert to grayscale for better OCR
            $image->greyscale();
            
            // Increase contrast
            $image->contrast(20);
            
            // Sharpen image
            $image->sharpen(10);
            
            // Adjust brightness if needed
            $image->brightness(5);
            
            // Save as PNG for better quality
            $image->save($optimizedFullPath, 100, 'png');
            
            return $optimizedPath;
            
        } catch (\Exception $e) {
            // If optimization fails, return original path
            return $imagePath;
        }
    }

    private function processOcr($imagePath, $hasHeader)
    {
        $fullPath = storage_path('app/' . $imagePath);
        
        if (!file_exists($fullPath)) {
            throw new \Exception('File gambar tidak ditemukan');
        }
        
        try {
            // Use Tesseract OCR with optimized settings for tables
            $tesseract = new TesseractOCR($fullPath);
            
            // Configure for better table recognition
            $tesseract->lang('eng+ind')  // English + Indonesian
                     ->psm(6)            // Assume a uniform block of text (for tables)
                     ->oem(3)            // Default OCR engine mode
                     ->configFile(storage_path('app/tesseract/config.txt'));
            
            // Add custom configurations for table recognition
            $tesseract->allowlist('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.,;:!?@#$%^&*()-_=+[]{}|\'"<>/\\ ')
                     ->userPatterns(storage_path('app/tesseract/patterns.txt'));
            
            $text = $tesseract->run();
            
            return $this->parseTableFromText($text, $hasHeader);
            
        } catch (\Exception $e) {
            throw new \Exception('OCR Processing Error: ' . $e->getMessage());
        }
    }

    private function parseTableFromText($text, $hasHeader)
    {
        $lines = explode("\n", trim($text));
        $data = ['columns' => [], 'rows' => []];
        
        if (empty($lines)) {
            return $data;
        }

        $maxColumns = 0;
        $parsedRows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Enhanced table parsing
            // Try to split by multiple spaces, tabs, or common table separators
            $cells = preg_split('/\s{2,}|\t|\|\s*|;\s*|,\s*(?=\S)/', $line);
            $cells = array_map('trim', $cells);
            $cells = array_filter($cells, function($cell) {
                return !empty($cell) && strlen($cell) < 500; // Filter out very long cells
            });
            
            if (!empty($cells)) {
                $parsedRows[] = array_values($cells);
                $maxColumns = max($maxColumns, count($cells));
            }
        }

        // If no rows parsed, return empty data
        if (empty($parsedRows)) {
            return $data;
        }

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

    private function importOcrData($dataset, $rows)
    {
        if (empty($rows)) {
            return 0;
        }
        
        $batchData = [];
        $importedCount = 0;
        
        foreach ($rows as $row) {
            $rowData = [];
            
            foreach ($dataset->columns as $colIndex => $column) {
                $value = $row[$colIndex] ?? null;
                $rowData[$column] = $this->cleanValue($value);
            }
            
            $batchData[] = [
                'dataset_id' => $dataset->id,
                'data' => json_encode($rowData),
                'created_at' => now(),
                'updated_at' => now()
            ];
            $importedCount++;
            
            if (count($batchData) >= 100) {
                DatasetRow::insert($batchData);
                $batchData = [];
            }
        }
        
        if (!empty($batchData)) {
            DatasetRow::insert($batchData);
        }
        
        $dataset->row_count = $importedCount;
        $dataset->save();
        
        return $importedCount;
    }

    private function cleanValue($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        $value = (string) $value;
        $value = trim($value);
        
        return $value;
    }
}