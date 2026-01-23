<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function datasets()
    {
        return $this->hasMany(Dataset::class);
    }

    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    public function ocrResults()
    {
        return $this->hasMany(OcrResult::class);
    }

    public function sharedDatasets()
    {
        return $this->belongsToMany(Dataset::class, 'dataset_shares')
                    ->withPivot('permission', 'shared_by')
                    ->withTimestamps();
    }

    public function datasetShares()
    {
        return $this->hasMany(DatasetShare::class, 'shared_by');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Get all accessible datasets (own + shared + public)
    public function accessibleDatasets()
    {
        // Own datasets
        $ownDatasetIds = $this->datasets()->pluck('id');
        
        // Shared datasets
        $sharedDatasetIds = $this->sharedDatasets()->pluck('datasets.id');
        
        // Public datasets (excluding own)
        $publicDatasetIds = Dataset::where(function($query) {
            $query->where('is_public', true)
                  ->orWhere('access_type', 'public');
        })
        ->whereNotIn('id', $ownDatasetIds)
        ->pluck('id');
        
        // Combine all dataset IDs
        $allDatasetIds = $ownDatasetIds
            ->merge($sharedDatasetIds)
            ->merge($publicDatasetIds)
            ->unique();
        
        return Dataset::whereIn('id', $allDatasetIds);
    }

    // Get users who can be shared with (excluding self and already shared)
    public function getShareableUsers($dataset = null)
    {
        $query = User::where('id', '!=', $this->id);
        
        if ($dataset) {
            // Exclude users already shared with this dataset
            $alreadyShared = $dataset->shares()->pluck('user_id');
            $query->whereNotIn('id', $alreadyShared);
        }
        
        return $query->orderBy('name')->get();
    }
}