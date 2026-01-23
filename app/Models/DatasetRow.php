<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }
}