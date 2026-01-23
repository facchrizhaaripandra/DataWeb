<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetMerge extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_dataset_id',
        'target_dataset_id',
        'source_type',
        'filename',
        'merged_columns',
        'rows_added',
        'rows_skipped',
        'duplicates_removed',
        'remove_duplicates',
        'status',
        'error_message',
        'user_id'
    ];

    protected $casts = [
        'merged_columns' => 'array'
    ];

    public function sourceDataset()
    {
        return $this->belongsTo(Dataset::class, 'source_dataset_id');
    }

    public function targetDataset()
    {
        return $this->belongsTo(Dataset::class, 'target_dataset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}