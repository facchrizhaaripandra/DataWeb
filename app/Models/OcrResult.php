<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OcrResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'detected_data',
        'status',
        'error_message',
        'user_id',
        'dataset_id',
        'has_header',
        'row_count'
    ];

    protected $casts = [
        'detected_data' => 'array',
        'has_header' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class);
    }

    // Accessor untuk URL gambar
    public function getImageUrlAttribute()
    {
        return Storage::url($this->image_path);
    }

    // Accessor untuk status badge
    public function getStatusBadgeAttribute()
    {
        $statusColors = [
            'pending' => 'warning',
            'processing' => 'info',
            'processed' => 'success',
            'failed' => 'danger'
        ];

        $statusIcons = [
            'pending' => 'clock',
            'processing' => 'sync',
            'processed' => 'check',
            'failed' => 'times'
        ];

        return sprintf(
            '<span class="badge bg-%s"><i class="fas fa-%s"></i> %s</span>',
            $statusColors[$this->status] ?? 'secondary',
            $statusIcons[$this->status] ?? 'question',
            ucfirst($this->status)
        );
    }
}