<?php

namespace App\Imports;

use App\Models\Dataset;
use App\Models\DatasetRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class DataImport implements ToCollection, WithHeadingRow
{
    protected $dataset;

    public function __construct(Dataset $dataset)
    {
        $this->dataset = $dataset;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $data = [];
            
            foreach ($this->dataset->columns as $column) {
                $data[$column] = $row[$column] ?? null;
            }
            
            DatasetRow::create([
                'dataset_id' => $this->dataset->id,
                'data' => $data
            ]);
        }
        
        $this->dataset->increment('row_count', count($rows));
    }
}