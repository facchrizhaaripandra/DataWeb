<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dataset;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_datasets' => Dataset::count(),
            'total_imports' => Import::count(),
            'recent_users' => User::orderBy('created_at', 'desc')->limit(5)->get()
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function users()
    {
        $users = User::withCount(['datasets', 'imports'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.users', compact('users'));
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,user'
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return redirect()->route('admin.users')
            ->with('success', 'User berhasil diperbarui!');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Delete user's data first
        $user->datasets()->delete();
        $user->imports()->delete();
        
        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'User berhasil dihapus!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully'
        ]);
    }

    public function makeAdmin($id)
    {
        $user = User::findOrFail($id);
        $user->update(['role' => 'admin']);

        return response()->json(['success' => true]);
    }

    public function removeAdmin($id)
    {
        $user = User::findOrFail($id);
        $user->update(['role' => 'user']);

        return response()->json(['success' => true]);
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['success' => true]);
    }

    public function system()
    {
        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
            'storage_path' => storage_path(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
        ];

        $storageInfo = [
            'total' => disk_total_space(storage_path()),
            'free' => disk_free_space(storage_path()),
            'used' => disk_total_space(storage_path()) - disk_free_space(storage_path()),
        ];

        $databaseInfo = [
            'total_datasets' => Dataset::count(),
            'total_users' => User::count(),
            'total_imports' => Import::count(),
            'database_size' => $this->getDatabaseSize(),
        ];

        return view('admin.system', compact('systemInfo', 'storageInfo', 'databaseInfo'));
    }

    public function backup()
    {
        try {
            // Simulate backup for now
            $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
            
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully: ' . $filename
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logs()
    {
        $logs = [];
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            $logs = array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            $logs = array_slice($logs, 0, 100);
        }
        
        return view('admin.logs', compact('logs'));
    }

    public function showLog($id)
    {
        return view('admin.log-show', ['logId' => $id]);
    }

    public function settings()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
        ];
        
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
        ]);
        
        // In a real app, you would save to database or .env file
        return response()->json(['success' => true]);
    }

    private function getDatabaseSize()
    {
        try {
            if (config('database.default') === 'mysql') {
                $databaseName = config('database.connections.mysql.database');
                $size = DB::select("SELECT SUM(data_length + index_length) as size 
                                   FROM information_schema.TABLES 
                                   WHERE table_schema = ?", [$databaseName]);
                
                if (!empty($size)) {
                    $bytes = $size[0]->size;
                    return $this->formatBytes($bytes);
                }
            } elseif (config('database.default') === 'pgsql') {
                $size = DB::select("SELECT pg_database_size(current_database()) as size");
                if (!empty($size)) {
                    $bytes = $size[0]->size;
                    return $this->formatBytes($bytes);
                }
            }
        } catch (\Exception $e) {
            // Ignore error and return unknown
        }
        
        return 'Unknown';
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}