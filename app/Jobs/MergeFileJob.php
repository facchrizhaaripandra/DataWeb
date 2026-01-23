<?php

namespace App\Jobs;

use App\Models\DatasetMerge;
use App\Models\DatasetRow;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MergeFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merge;

    public function __construct(DatasetMerge $merge)
    {
        $this->merge = $merge;
    }

    public function handle()
    {
        try {
            $targetDataset = $this->merge->targetDataset;
            $fileColumns = $this->merge->merged_columns;
            
            // Baca file
            $path = storage_path('app/merges/' . $this->merge->filename);
            $data = Excel::toArray([], $path);
            
            $allRows = $data[0];
            
            // Lewati header jika ada
            if ($this->merge->has_header) {
                $allRows = array_slice($allRows, 1);
            }
            
            // Mapping kolom
            $columnMapping = [];
            foreach ($targetDataset->columns as $targetColumn) {
                foreach ($fileColumns as $sourceColumn) {
                    if (strtolower($sourceColumn) === strtolower($targetColumn)) {
                        $columnMapping[] = ['source' => $sourceColumn, 'target' => $targetColumn];
                        break;
                    }
                }
            }
            
            $rowsAdded = 0;
            $duplicatesRemoved = 0;
            
            // Jika remove_duplicates true
            if ($this->merge->remove_duplicates) {
                $existingRows = DatasetRow::where('dataset_id', $targetDataset->id)
                    ->get()
                    ->map(function($row) use ($targetDataset) {
                        return $this->createRowSignature($row->data, $targetDataset->columns);
                    })
                    ->toArray();
                
                foreach ($allRows as $row) {
                    $rowData = [];
                    foreach ($columnMapping as $mapping) {
                        $sourceIndex = array_search($mapping['source'], $fileColumns);
                        if ($sourceIndex !== false && isset($row[$sourceIndex])) {
                            $rowData[$mapping['target']] = $row[$sourceIndex];
                        } else {
                            $rowData[$mapping['target']] = null;
                        }
                    }
                    
                    // Cek duplikasi
                    $signature = $this->createRowSignature($rowData, $targetDataset->columns);
                    if (in_array($signature, $existingRows)) {
                        $duplicatesRemoved++;
                        continue;
                    }
                    
                    DatasetRow::create([
                        'dataset_id' => $targetDataset->id,
                        'data' => $rowData
                    ]);
                    
                    $rowsAdded++;
                    $existingRows[] = $signature;
                }
            } else {
                // Tanpa cek duplikasi
                foreach ($allRows as $row) {
                    $rowData = [];
                    foreach ($columnMapping as $mapping) {
                        $sourceIndex = array_search($mapping['source'], $fileColumns);
                        if ($sourceIndex !== false && isset($row[$sourceIndex])) {
                            $rowData[$mapping['target']] = $row[$sourceIndex];
                        } else {
                            $rowData[$mapping['target']] = null;
                        }
                    }
                    
                    DatasetRow::create([
                        'dataset_id' => $targetDataset->id,
                        'data' => $rowData
                    ]);
                    
                    $rowsAdded++;
                }
            }
            
            // Update dataset
            $targetDataset->row_count = DatasetRow::where('dataset_id', $targetDataset->id)->count();
            $targetDataset->save();
            
            // Update merge record
            $this->merge->update([
                'status' => 'completed',
                'rows_added' => $rowsAdded,
                'duplicates_removed' => $duplicatesRemoved
            ]);
            
            // Hapus file setelah selesai
            Storage::delete('merges/' . $this->merge->filename);
            
        } catch (\Exception $e) {
            $this->merge->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
    
    private function createRowSignature($rowData, $columns)
    {
        $values = [];
        foreach ($columns as $column) {
            $values[] = strtolower(trim($rowData[$column] ?? ''));
        }
        return md5(implode('|', $values));
    }
}