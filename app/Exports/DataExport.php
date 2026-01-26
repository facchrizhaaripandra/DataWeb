<?php

namespace App\Exports;

use App\Models\Dataset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataExport implements FromCollection, WithHeadings
{
    protected $dataset;

    public function __construct(Dataset $dataset)
    {
        $this->dataset = $dataset;
    }

    public function collection()
    {
        return $this->dataset->rows->map(function ($row) {
            $data = [];
            foreach ($this->dataset->column_definitions as $column) {
                $columnName = $column['name'];
                $value = $row->data[$columnName] ?? '';

                // Handle boolean values
                if ($column['type'] === 'boolean') {
                    $data[$columnName] = $value ? '1' : '0';
                } else {
                    $data[$columnName] = $value;
                }
            }
            return $data;
        });
    }

    public function headings(): array
    {
        return collect($this->dataset->column_definitions)->pluck('name')->toArray();
    }
}
