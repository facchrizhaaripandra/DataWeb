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
        'is_public',
        'access_type',
        'shared_with',
        'columns',
        'row_count',
        'user_id'
    ];

    protected $casts = [
        'columns' => 'array',
        'shared_with' => 'array',
        'is_public' => 'boolean'
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

    public function shares()
    {
        return $this->hasMany(DatasetShare::class);
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'dataset_shares')
                    ->withPivot('permission', 'shared_by')
                    ->withTimestamps();
    }

    // Check if user has access to this dataset
    public function hasAccess($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        // Owner always has access
        if ($this->user_id === $user->id) {
            return true;
        }

        // Admin can view all datasets
        if ($user->isAdmin()) {
            return true;
        }

        // Check if dataset is public
        if ($this->is_public || $this->access_type === 'public') {
            return true;
        }

        // Check if user is in shared_with array
        if ($this->shared_with && in_array($user->id, $this->shared_with)) {
            return true;
        }

        // Check dataset_shares table
        return $this->shares()->where('user_id', $user->id)->exists();
    }

    // Check if user has edit permission for dataset metadata (only owner and admin)
    public function canEditDataset($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        // Owner can always edit dataset
        if ($this->user_id === $user->id) {
            return true;
        }

        // Admin can edit all datasets
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    // Check if user has edit permission (for rows and data)
    public function canEdit($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return false;
        }

        // Owner can always edit
        if ($this->user_id === $user->id) {
            return true;
        }

        // Admin can edit all datasets
        if ($user->isAdmin()) {
            return true;
        }

        // Check permission in dataset_shares table
        $share = $this->shares()->where('user_id', $user->id)->first();

        if ($share) {
            return in_array($share->permission, ['edit', 'owner']);
        }

        return false;
    }

    // Share dataset with user
    public function shareWith($userId, $permission = 'view', $sharedBy = null)
    {
        if (!$sharedBy) {
            $sharedBy = auth()->id();
        }

        // Add to shared_with array
        $sharedWith = $this->shared_with ?? [];
        if (!in_array($userId, $sharedWith)) {
            $sharedWith[] = $userId;
            $this->shared_with = $sharedWith;
            $this->save();
        }

        // Create or update share record
        return DatasetShare::updateOrCreate(
            [
                'dataset_id' => $this->id,
                'user_id' => $userId
            ],
            [
                'permission' => $permission,
                'shared_by' => $sharedBy
            ]
        );
    }

    // Remove share with user
    public function removeShare($userId)
    {
        // Remove from shared_with array
        $sharedWith = $this->shared_with ?? [];
        $sharedWith = array_diff($sharedWith, [$userId]);
        $this->shared_with = array_values($sharedWith);
        $this->save();

        // Remove from shares table
        return $this->shares()->where('user_id', $userId)->delete();
    }

    // Get all users who have access
    public function getUsersWithAccess()
    {
        $users = collect();

        // Add owner
        if ($this->user) {
            $users->push([
                'user' => $this->user,
                'permission' => 'owner',
                'shared_by' => null
            ]);
        }

        // Add shared users
        foreach ($this->shares as $share) {
            if ($share->user) {
                $users->push([
                    'user' => $share->user,
                    'permission' => $share->permission,
                    'shared_by' => $share->sharer
                ]);
            }
        }

        return $users;
    }
}