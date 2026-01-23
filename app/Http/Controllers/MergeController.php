<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\DatasetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MergeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Method untuk menampilkan halaman merge
    public function index()
    {
        $datasets = Dataset::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('merge.index', compact('datasets'));
    }

    // Method untuk menampilkan form merge
    public function create()
    {
        $datasets = Dataset::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('merge.create', compact('datasets'));
    }

    // Method untuk proses merge
    public function store(Request $request)
    {
        $request->validate([
            'dataset1_id' => 'required|exists:datasets,id',
            'dataset2_id' => 'required|exists:datasets,id',
            'new_dataset_name' => 'required|string|max:255',
            'merge_type' => 'required|in:union,join,concatenate'
        ]);

        $dataset1 = Dataset::where('user_id', auth()->id())
            ->findOrFail($request->dataset1_id);
        $dataset2 = Dataset::where('user_id', auth()->id())
            ->findOrFail($request->dataset2_id);

        try {
            DB::beginTransaction();

            switch ($request->merge_type) {
                case 'union':
                    $newDataset = $this->mergeUnion($dataset1, $dataset2, $request->new_dataset_name);
                    break;
                
                case 'join':
                    $newDataset = $this->mergeJoin($dataset1, $dataset2, $request->new_dataset_name);
                    break;
                
                case 'concatenate':
                    $newDataset = $this->mergeConcatenate($dataset1, $dataset2, $request->new_dataset_name);
                    break;
                
                default:
                    throw new \Exception('Merge type not supported');
            }

            DB::commit();

            return redirect()->route('datasets.show', $newDataset->id)
                ->with('success', 'Dataset berhasil digabungkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Gagal menggabungkan dataset: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Union merge: menggabungkan baris dari dua dataset dengan kolom yang sama
    private function mergeUnion($dataset1, $dataset2, $newName)
    {
        // Gabungkan semua kolom unik dari kedua dataset
        $allColumns = array_unique(array_merge($dataset1->columns, $dataset2->columns));
        
        // Buat dataset baru
        $newDataset = Dataset::create([
            'name' => $newName,
            'description' => 'Hasil merge union dari: ' . $dataset1->name . ' dan ' . $dataset2->name,
            'columns' => $allColumns,
            'user_id' => auth()->id(),
            'row_count' => 0
        ]);

        // Import data dari dataset 1
        $this->importDatasetRows($newDataset, $dataset1->rows, $allColumns);
        
        // Import data dari dataset 2
        $this->importDatasetRows($newDataset, $dataset2->rows, $allColumns);

        return $newDataset;
    }

    // Join merge: menggabungkan kolom dari dua dataset berdasarkan key tertentu
    private function mergeJoin($dataset1, $dataset2, $newName)
    {
        // Gabungkan semua kolom (hilangkan duplikat)
        $allColumns = array_values(array_unique(array_merge($dataset1->columns, $dataset2->columns)));
        
        $newDataset = Dataset::create([
            'name' => $newName,
            'description' => 'Hasil merge join dari: ' . $dataset1->name . ' dan ' . $dataset2->name,
            'columns' => $allColumns,
            'user_id' => auth()->id(),
            'row_count' => 0
        ]);

        // Untuk join sederhana, kita asumsikan dataset memiliki jumlah baris yang sama
        $rows1 = $dataset1->rows()->get();
        $rows2 = $dataset2->rows()->get();
        
        $count = min(count($rows1), count($rows2));
        
        for ($i = 0; $i < $count; $i++) {
            $rowData = [];
            
            // Data dari dataset 1
            foreach ($dataset1->columns as $column) {
                $rowData[$column] = $rows1[$i]->data[$column] ?? null;
            }
            
            // Data dari dataset 2
            foreach ($dataset2->columns as $column) {
                if (!array_key_exists($column, $rowData)) {
                    $rowData[$column] = $rows2[$i]->data[$column] ?? null;
                }
            }
            
            DatasetRow::create([
                'dataset_id' => $newDataset->id,
                'data' => $rowData
            ]);
        }
        
        $newDataset->row_count = $count;
        $newDataset->save();

        return $newDataset;
    }

    // Concatenate merge: menggabungkan kolom secara horizontal
    private function mergeConcatenate($dataset1, $dataset2, $newName)
    {
        // Gabungkan nama kolom dengan prefix
        $columns1 = array_map(function($col) use ($dataset1) {
            return $dataset1->name . '_' . $col;
        }, $dataset1->columns);
        
        $columns2 = array_map(function($col) use ($dataset2) {
            return $dataset2->name . '_' . $col;
        }, $dataset2->columns);
        
        $allColumns = array_merge($columns1, $columns2);
        
        $newDataset = Dataset::create([
            'name' => $newName,
            'description' => 'Hasil merge concatenate dari: ' . $dataset1->name . ' dan ' . $dataset2->name,
            'columns' => $allColumns,
            'user_id' => auth()->id(),
            'row_count' => 0
        ]);

        // Untuk concatenate, kita gabungkan baris per baris
        $rows1 = $dataset1->rows()->get();
        $rows2 = $dataset2->rows()->get();
        
        $count = min(count($rows1), count($rows2));
        
        for ($i = 0; $i < $count; $i++) {
            $rowData = [];
            
            // Data dari dataset 1 dengan prefix
            foreach ($dataset1->columns as $index => $column) {
                $newColumnName = $columns1[$index];
                $rowData[$newColumnName] = $rows1[$i]->data[$column] ?? null;
            }
            
            // Data dari dataset 2 dengan prefix
            foreach ($dataset2->columns as $index => $column) {
                $newColumnName = $columns2[$index];
                $rowData[$newColumnName] = $rows2[$i]->data[$column] ?? null;
            }
            
            DatasetRow::create([
                'dataset_id' => $newDataset->id,
                'data' => $rowData
            ]);
        }
        
        $newDataset->row_count = $count;
        $newDataset->save();

        return $newDataset;
    }

    // Helper untuk import rows
    private function importDatasetRows($newDataset, $rows, $columns)
    {
        $batchData = [];
        
        foreach ($rows as $row) {
            $rowData = [];
            
            foreach ($columns as $column) {
                $rowData[$column] = $row->data[$column] ?? null;
            }
            
            $batchData[] = [
                'dataset_id' => $newDataset->id,
                'data' => json_encode($rowData),
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if (count($batchData) >= 100) {
                DatasetRow::insert($batchData);
                $batchData = [];
            }
        }
        
        if (!empty($batchData)) {
            DatasetRow::insert($batchData);
        }
        
        // Update row count
        $newDataset->increment('row_count', count($rows));
    }

    // Preview merge
    public function preview(Request $request)
    {
        $request->validate([
            'dataset1_id' => 'required|exists:datasets,id',
            'dataset2_id' => 'required|exists:datasets,id',
            'merge_type' => 'required|in:union,join,concatenate'
        ]);

        $dataset1 = Dataset::where('user_id', auth()->id())
            ->findOrFail($request->dataset1_id);
        $dataset2 = Dataset::where('user_id', auth()->id())
            ->findOrFail($request->dataset2_id);

        $previewData = [
            'dataset1' => [
                'name' => $dataset1->name,
                'columns' => $dataset1->columns,
                'row_count' => $dataset1->row_count,
                'sample_data' => $dataset1->rows()->limit(3)->get()
            ],
            'dataset2' => [
                'name' => $dataset2->name,
                'columns' => $dataset2->columns,
                'row_count' => $dataset2->row_count,
                'sample_data' => $dataset2->rows()->limit(3)->get()
            ],
            'merge_type' => $request->merge_type,
            'result_columns' => [],
            'result_sample' => []
        ];

        // Generate preview berdasarkan merge type
        switch ($request->merge_type) {
            case 'union':
                $previewData['result_columns'] = array_unique(array_merge($dataset1->columns, $dataset2->columns));
                break;
            
            case 'join':
                $previewData['result_columns'] = array_values(array_unique(array_merge($dataset1->columns, $dataset2->columns)));
                break;
            
            case 'concatenate':
                $columns1 = array_map(function($col) use ($dataset1) {
                    return $dataset1->name . '_' . $col;
                }, $dataset1->columns);
                
                $columns2 = array_map(function($col) use ($dataset2) {
                    return $dataset2->name . '_' . $col;
                }, $dataset2->columns);
                
                $previewData['result_columns'] = array_merge($columns1, $columns2);
                break;
        }

        return response()->json([
            'success' => true,
            'preview' => $previewData
        ]);
    }
}