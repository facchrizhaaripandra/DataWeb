<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\User;
use App\Models\DatasetShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DatasetShareController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Show share form
    public function create($id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        $shareableUsers = auth()->user()->getShareableUsers($dataset);
        
        return view('datasets.share', compact('dataset', 'shareableUsers'));
    }

    // Share dataset with single user
    public function store(Request $request, $id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'user_identifier' => 'required|string',
            'permission' => 'required|in:view,edit'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Find user by email or username
        $userIdentifier = $request->user_identifier;
        $user = User::where('email', $userIdentifier)
            ->orWhere('name', $userIdentifier)
            ->first();
        
        if (!$user) {
            return redirect()->back()
                ->with('error', 'User tidak ditemukan. Gunakan email atau username yang valid.')
                ->withInput();
        }
        
        // Check if user is trying to share with themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak bisa membagikan dataset ke diri sendiri.')
                ->withInput();
        }
        
        // Check if already shared
        $existingShare = DatasetShare::where('dataset_id', $dataset->id)
            ->where('user_id', $user->id)
            ->first();
        
        if ($existingShare) {
            return redirect()->back()
                ->with('error', 'Dataset sudah dibagikan dengan user ini.')
                ->withInput();
        }
        
        // Share dataset
        $dataset->shareWith($user->id, $request->permission, auth()->id());
        
        return redirect()->route('datasets.show', $dataset->id)
            ->with('success', 'Dataset berhasil dibagikan dengan ' . $user->name . ' (' . $user->email . ')');
    }

    // Show shared users for a dataset
    public function show($id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);

        // Get all users with access to this dataset
        $sharedUsers = collect();

        // Add owner
        $sharedUsers->push([
            'user' => $dataset->user,
            'permission' => 'owner',
            'shared_by' => null,
            'shared_at' => $dataset->created_at
        ]);

        // Add shared users
        foreach ($dataset->shares()->with('user', 'sharer')->get() as $share) {
            if ($share->user) {
                $sharedUsers->push([
                    'user' => $share->user,
                    'permission' => $share->permission,
                    'shared_by' => $share->sharer,
                    'shared_at' => $share->created_at
                ]);
            }
        }

        return view('datasets.shared-users', compact('dataset', 'sharedUsers'));
    }

    // Update share permission
    public function update(Request $request, $datasetId, $userId)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($datasetId);
        
        $validator = Validator::make($request->all(), [
            'permission' => 'required|in:view,edit'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Update share permission
        $share = DatasetShare::where('dataset_id', $dataset->id)
            ->where('user_id', $userId)
            ->firstOrFail();
        
        $share->update(['permission' => $request->permission]);
        
        return response()->json(['success' => true]);
    }

    // Remove share
    public function destroy($datasetId, $userId)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($datasetId);
        
        // Don't allow removing owner
        if ($dataset->user_id == $userId) {
            return response()->json(['error' => 'Tidak bisa menghapus owner dataset'], 403);
        }
        
        $dataset->removeShare($userId);
        
        return response()->json(['success' => true]);
    }

    // Bulk share with multiple users
    public function bulkShare(Request $request, $id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'users' => 'required|array',
            'users.*' => 'required|string',
            'permission' => 'required|in:view,edit'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $successCount = 0;
        $errorUsers = [];
        
        foreach ($request->users as $userIdentifier) {
            $user = User::where('email', $userIdentifier)
                ->orWhere('name', $userIdentifier)
                ->first();
            
            if (!$user) {
                $errorUsers[] = $userIdentifier . ' (tidak ditemukan)';
                continue;
            }
            
            if ($user->id === auth()->id()) {
                $errorUsers[] = $userIdentifier . ' (diri sendiri)';
                continue;
            }
            
            // Check if already shared
            $existingShare = DatasetShare::where('dataset_id', $dataset->id)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existingShare) {
                $errorUsers[] = $userIdentifier . ' (sudah dibagikan)';
                continue;
            }
            
            // Share dataset
            $dataset->shareWith($user->id, $request->permission, auth()->id());
            $successCount++;
        }
        
        $message = 'Berhasil membagikan dataset ke ' . $successCount . ' user.';
        
        if (!empty($errorUsers)) {
            $message .= ' Gagal untuk: ' . implode(', ', $errorUsers);
        }
        
        return redirect()->route('datasets.show', $dataset->id)
            ->with('success', $message);
    }
}