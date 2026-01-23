<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'user_id',
        'shared_by',
        'permission'
    ];

    protected $casts = [
        'permission' => 'string'
    ];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sharer()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }
}