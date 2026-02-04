<?php
// app/Http\Controllers\ImportController.php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\Dataset;
use App\Models\DatasetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Method untuk menampilkan halaman create
    public function create()
    {
        $datasets = Dataset::where('user_id', auth()->id())->get();
        return view('imports.create', compact('datasets'));
    }

    // Method untuk store/import data
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'dataset_name' => 'required_if:dataset_id,null|string|max:255',
            'has_header' => 'required|boolean'
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . Str::random(10) . '_' . $originalName;
        $path = $file->storeAs('imports', $filename);

        try {
            // Read file data
            $data = Excel::toArray([], $file);
            
            if (empty($data[0])) {
                throw new \Exception('File kosong atau format tidak sesuai');
            }

            $allRows = $data[0];
            $hasHeader = $request->has_header;
            
            // Determine column names
            $columns = [];
            $dataRows = [];
            
            if ($hasHeader && !empty($allRows)) {
                // Use first row as headers
                $firstRow = $allRows[0];
                $columns = array_values($firstRow);
                // Remove header row from data
                $dataRows = array_slice($allRows, 1);
            } else {
                // Generate column names
                if (!empty($allRows)) {
                    $firstRow = $allRows[0];
                    $columnCount = count($firstRow);
                    for ($i = 1; $i <= $columnCount; $i++) {
                        $columns[] = 'Kolom ' . $i;
                    }
                    $dataRows = $allRows;
                }
            }

            // Clean column names
            $columns = array_map(function($column) {
                return $this->cleanColumnName($column);
            }, $columns);

            // Create or get dataset
            $dataset = null;
            if ($request->dataset_id) {
                $dataset = Dataset::where('user_id', auth()->id())
                    ->findOrFail($request->dataset_id);
                
                // Add new columns if they don't exist
                $existingColumns = $dataset->columns ?? [];
                $newColumns = array_diff($columns, $existingColumns);
                
                if (!empty($newColumns)) {
                    $dataset->columns = array_merge($existingColumns, $newColumns);
                    $dataset->save();
                }
            } else {
                $dataset = Dataset::create([
                    'name' => $request->dataset_name,
                    'description' => 'Diimport dari: ' . $originalName . 
                                   ($hasHeader ? ' (dengan header)' : ' (tanpa header)'),
                    'columns' => $columns,
                    'user_id' => auth()->id(),
                    'row_count' => 0
                ]);
            }

            // Create import record
            $import = Import::create([
                'filename' => $filename,
                'original_name' => $originalName,
                'status' => 'completed',
                'user_id' => auth()->id(),
                'dataset_id' => $dataset->id,
                'row_count' => count($dataRows),
                'has_header' => $hasHeader
            ]);

            // Import data rows
            $importedCount = $this->importDataRows($dataset, $dataRows, $columns);

            return redirect()->route('datasets.show', $dataset->id)
                ->with('success', 'File berhasil diimport! ' . $importedCount . ' baris ditambahkan.');

        } catch (\Exception $e) {
            // Clean up file if exists
            if (isset($filename) && Storage::exists('imports/' . $filename)) {
                Storage::delete('imports/' . $filename);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal mengimport file: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Method untuk preview file
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'has_header' => 'required|boolean'
        ]);

        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file);
            
            if (empty($data[0])) {
                return response()->json([
                    'success' => false,
                    'message' => 'File kosong'
                ]);
            }

            $allRows = $data[0];
            $hasHeader = $request->has_header;
            
            $columns = [];
            $previewRows = [];
            
            if ($hasHeader && !empty($allRows)) {
                $firstRow = $allRows[0];
                $columns = array_values($firstRow);
                $previewRows = array_slice($allRows, 1, 5);
            } else {
                if (!empty($allRows)) {
                    $firstRow = $allRows[0];
                    $columnCount = count($firstRow);
                    for ($i = 1; $i <= $columnCount; $i++) {
                        $columns[] = 'Kolom ' . $i;
                    }
                    $previewRows = array_slice($allRows, 0, 5);
                }
            }
            
            // Clean column names for preview
            $columns = array_map(function($column) {
                return $this->cleanColumnName($column);
            }, $columns);

            return response()->json([
                'success' => true,
                'columns' => $columns,
                'total_rows' => $hasHeader ? count($allRows) - 1 : count($allRows),
                'preview' => array_slice($previewRows, 0, 5),
                'has_header' => $hasHeader
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage()
            ], 400);
        }
    }

    // Method untuk menampilkan daftar imports
    public function index()
    {
        $imports = Import::where('user_id', auth()->id())
            ->with('dataset')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('imports.index', compact('imports'));
    }

    // Method untuk menampilkan detail import
    public function show($id)
    {
        $import = Import::where('user_id', auth()->id())
            ->with('dataset')
            ->findOrFail($id);
        
        return view('imports.show', compact('import'));
    }

    // Method untuk retry import
    public function retry($id)
    {
        $import = Import::where('user_id', auth()->id())->findOrFail($id);
        
        try {
            // Logic untuk retry import
            $import->update(['status' => 'processing']);
            
            // Process the import again
            // ... your retry logic here ...
            
            $import->update(['status' => 'completed']);
            
            return response()->json([
                'success' => true,
                'message' => 'Import berhasil diulang'
            ]);
            
        } catch (\Exception $e) {
            $import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengulang import: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk menghapus import
    public function destroy($id)
    {
        $import = Import::where('user_id', auth()->id())->findOrFail($id);
        
        // Delete file from storage
        if (Storage::exists('imports/' . $import->filename)) {
            Storage::delete('imports/' . $import->filename);
        }
        
        $import->delete();
        
        return redirect()->route('imports.index')
            ->with('success', 'Import record berhasil dihapus');
    }

    // Method untuk check status import (API)
    public function checkStatus($id)
    {
        $import = Import::where('user_id', auth()->id())->findOrFail($id);
        
        return response()->json([
            'status' => $import->status,
            'row_count' => $import->row_count,
            'error_message' => $import->error_message,
            'created_at' => $import->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $import->updated_at->format('Y-m-d H:i:s'),
        ]);
    }

    // Helper methods
    private function cleanColumnName($column)
    {
        // Remove special characters and trim
        $column = preg_replace('/[^\p{L}\p{N}\s]/u', '', $column);
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

    private function importDataRows($dataset, $rows, $columns)
    {
        $batchSize = 100;
        $batchData = [];
        $importedCount = 0;
        
        foreach ($rows as $index => $row) {
            $rowData = [];
            
            foreach ($columns as $colIndex => $column) {
                // Handle both associative and indexed arrays
                $value = null;
                
                if (is_array($row)) {
                    if (array_key_exists($colIndex, $row)) {
                        $value = $row[$colIndex];
                    } elseif (array_key_exists($column, $row)) {
                        $value = $row[$column];
                    }
                } elseif (is_object($row)) {
                    $value = $row->$colIndex ?? $row->$column ?? null;
                }
                
                // Convert to string and clean
                $rowData[$column] = $this->cleanValue($value);
            }
            
            $batchData[] = [
                'dataset_id' => $dataset->id,
                'data' => json_encode($rowData),
                'created_at' => now(),
                'updated_at' => now()
            ];
            $importedCount++;
            
            // Insert in batches for performance
            if (count($batchData) >= $batchSize) {
                DatasetRow::insert($batchData);
                $batchData = [];
            }
        }
        
        // Insert remaining data
        if (!empty($batchData)) {
            DatasetRow::insert($batchData);
        }
        
        // Update row count
        $dataset->row_count = $importedCount;
        $dataset->save();
        
        return $importedCount;
    }

    private function cleanValue($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        // Convert to string
        $value = (string) $value;
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }
}