<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_name',
        'row_count',
        'status',
        'error_message',
        'user_id',
        'dataset_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }
}