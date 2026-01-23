<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'columns',
        'row_count',
        'user_id'
    ];

    protected $casts = [
        'columns' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rows()
    {
        return $this->hasMany(DatasetRow::class);
    }

    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    public function ocrResults()
    {
        return $this->hasMany(OcrResult::class);
    }
}