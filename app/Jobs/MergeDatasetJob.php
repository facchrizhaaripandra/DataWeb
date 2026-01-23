<?php

namespace App\Jobs;

use App\Models\DatasetMerge;
use App\Models\DatasetRow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MergeDatasetJob implements ShouldQueue
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
            $sourceDataset = $this->merge->sourceDataset;
            $targetDataset = $this->merge->targetDataset;
            
            // Mapping kolom (case insensitive)
            $columnMapping = [];
            foreach ($targetDataset->columns as $targetColumn) {
                foreach ($sourceDataset->columns as $sourceColumn) {
                    if (strtolower($sourceColumn) === strtolower($targetColumn)) {
                        $columnMapping[$sourceColumn] = $targetColumn;
                        break;
                    }
                }
            }
            
            $rowsAdded = 0;
            $rowsSkipped = 0;
            $duplicatesRemoved = 0;
            
            // Ambil semua rows dari source dataset
            $sourceRows = DatasetRow::where('dataset_id', $sourceDataset->id)->get();
            
            // Jika remove_duplicates true, kita perlu cek duplikasi
            if ($this->merge->remove_duplicates) {
                // Ambil existing rows untuk comparison
                $existingRows = DatasetRow::where('dataset_id', $targetDataset->id)
                    ->get()
                    ->map(function($row) use ($targetDataset) {
                        return $this->createRowSignature($row->data, $targetDataset->columns);
                    })
                    ->toArray();
                
                foreach ($sourceRows as $sourceRow) {
                    $rowData = [];
                    foreach ($columnMapping as $sourceCol => $targetCol) {
                        $rowData[$targetCol] = $sourceRow->data[$sourceCol] ?? null;
                    }
                    
                    // Buat signature untuk cek duplikasi
                    $signature = $this->createRowSignature($rowData, $targetDataset->columns);
                    
                    if (in_array($signature, $existingRows)) {
                        $duplicatesRemoved++;
                        continue;
                    }
                    
                    // Tambahkan row baru
                    DatasetRow::create([
                        'dataset_id' => $targetDataset->id,
                        'data' => $rowData
                    ]);
                    
                    $rowsAdded++;
                    $existingRows[] = $signature;
                }
            } else {
                // Tanpa cek duplikasi, langsung insert semua
                foreach ($sourceRows as $sourceRow) {
                    $rowData = [];
                    foreach ($columnMapping as $sourceCol => $targetCol) {
                        $rowData[$targetCol] = $sourceRow->data[$sourceCol] ?? null;
                    }
                    
                    DatasetRow::create([
                        'dataset_id' => $targetDataset->id,
                        'data' => $rowData
                    ]);
                    
                    $rowsAdded++;
                }
            }
            
            // Update target dataset row count
            $targetDataset->row_count = DatasetRow::where('dataset_id', $targetDataset->id)->count();
            $targetDataset->save();
            
            // Update merge record
            $this->merge->update([
                'status' => 'completed',
                'rows_added' => $rowsAdded,
                'rows_skipped' => $rowsSkipped,
                'duplicates_removed' => $duplicatesRemoved
            ]);
            
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