<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;

// Public Routes - No Authentication Required
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout Route - Accessible for authenticated users
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes - Require Authentication
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    
    // Dataset Routes (CRUD)
    Route::resource('datasets', DatasetController::class);
    
    // Dataset Row Operations
    Route::post('/datasets/{id}/add-row', [DatasetController::class, 'addRow'])->name('datasets.addRow');
    Route::get('/datasets/{datasetId}/rows/{rowId}/edit-form', [DatasetController::class, 'editRowForm'])->name('datasets.row.edit.form');
    Route::put('/datasets/{datasetId}/rows/{rowId}', [DatasetController::class, 'editRow'])->name('datasets.editRow');
    Route::delete('/datasets/{datasetId}/rows/{rowId}', [DatasetController::class, 'deleteRow'])->name('datasets.deleteRow');
    
    // Dataset Bulk Row Operations
    Route::post('/datasets/{id}/rows/delete-selected', [DatasetController::class, 'deleteSelectedRows'])->name('datasets.rows.delete.selected');
    Route::post('/datasets/{id}/rows/duplicate', [DatasetController::class, 'duplicateRows'])->name('datasets.rows.duplicate');
    
    // Dataset Column Operations
    Route::post('/datasets/{id}/add-column', [DatasetController::class, 'addColumn'])->name('datasets.addColumn');
    Route::post('/datasets/{id}/rename-column', [DatasetController::class, 'renameColumn'])->name('datasets.renameColumn');
    Route::delete('/datasets/{id}/delete-column', [DatasetController::class, 'deleteColumn'])->name('datasets.deleteColumn');
    Route::post('/datasets/{id}/reorder-columns', [DatasetController::class, 'reorderColumns'])->name('datasets.reorderColumns');
    
    // Dataset Import/Export/Analysis
    Route::post('/datasets/{id}/import', [DatasetController::class, 'importExcel'])->name('datasets.importExcel');
    Route::get('/datasets/{id}/export/{format?}', [DatasetController::class, 'export'])->name('datasets.export');
    Route::get('/datasets/{id}/analyze', [DatasetController::class, 'analyze'])->name('datasets.analyze');
    
    // Dataset Preview Operations
    Route::post('/datasets/preview-import', [DatasetController::class, 'previewImport'])->name('datasets.preview.import');
    Route::post('/datasets/preview-ocr', [DatasetController::class, 'previewOcr'])->name('datasets.preview.ocr');
    
    // Import Routes
    Route::prefix('imports')->name('imports.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::get('/create', [ImportController::class, 'create'])->name('create');
        Route::post('/preview', [ImportController::class, 'preview'])->name('preview');
        Route::post('/', [ImportController::class, 'store'])->name('store');
        Route::get('/{id}', [ImportController::class, 'show'])->name('show');
        Route::post('/{id}/retry', [ImportController::class, 'retry'])->name('retry');
        Route::delete('/{id}', [ImportController::class, 'destroy'])->name('destroy');
    });
    
    // OCR Routes
    Route::prefix('ocr')->name('ocr.')->group(function () {
        Route::get('/', [OcrController::class, 'index'])->name('index');
        Route::get('/create', [OcrController::class, 'create'])->name('create');
        Route::post('/preview', [OcrController::class, 'preview'])->name('preview');
        Route::post('/', [OcrController::class, 'store'])->name('store');
        Route::get('/{id}', [OcrController::class, 'show'])->name('show');
        Route::post('/{id}/retry', [OcrController::class, 'retry'])->name('retry');
        Route::post('/{id}/save', [OcrController::class, 'saveToDataset'])->name('save');
        Route::delete('/{id}', [OcrController::class, 'destroy'])->name('destroy');
    });
    
    // Admin Routes - Require Admin Role
    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::post('/users', [AdminController::class, 'store'])->name('users.store');
        Route::post('/users/{id}/make-admin', [AdminController::class, 'makeAdmin'])->name('users.make.admin');
        Route::post('/users/{id}/remove-admin', [AdminController::class, 'removeAdmin'])->name('users.remove.admin');
        Route::put('/users/{id}/password', [AdminController::class, 'updatePassword'])->name('users.update.password');
        
        // System Management
        Route::get('/system', [AdminController::class, 'system'])->name('system');
        Route::post('/system/backup', [AdminController::class, 'backup'])->name('system.backup');
        Route::post('/system/clear-cache', [AdminController::class, 'clearCache'])->name('system.clear.cache');
        
        // Activity Logs
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
        Route::get('/logs/{id}', [AdminController::class, 'showLog'])->name('logs.show');
        
        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });
    
    // API Routes for AJAX calls
    Route::prefix('api')->name('api.')->group(function () {
        // Dataset API
        Route::get('/datasets/{id}/stats', [DatasetController::class, 'getStats'])->name('datasets.stats');
        Route::get('/datasets/{id}/rows', [DatasetController::class, 'getRows'])->name('datasets.rows');
        Route::post('/datasets/{id}/search', [DatasetController::class, 'search'])->name('datasets.search');
        
        // Import API
        Route::get('/imports/status/{id}', [ImportController::class, 'checkStatus'])->name('imports.status');
        
        // OCR API
        Route::get('/ocr/status/{id}', [OcrController::class, 'checkStatus'])->name('ocr.status');
        
        // Analytics API
        Route::get('/analytics/datasets', [DashboardController::class, 'datasetAnalytics'])->name('analytics.datasets');
        Route::get('/analytics/imports', [DashboardController::class, 'importAnalytics'])->name('analytics.imports');
        Route::get('/analytics/ocr', [DashboardController::class, 'ocrAnalytics'])->name('analytics.ocr');
        
        // Admin API
        Route::get('/admin/backups', [AdminController::class, 'getBackups'])->name('admin.backups');
        Route::get('/admin/stats', [AdminController::class, 'getStats'])->name('admin.stats');
    });
    
    // Fallback Route for authenticated users
    Route::fallback(function () {
        return redirect()->route('dashboard');
    });
});

// Public API Routes (if needed for external access)
Route::prefix('public')->name('public.')->group(function () {
    // Example: Public dataset view (read-only)
    Route::get('/dataset/{id}', [DatasetController::class, 'publicView'])->name('dataset.view');
    
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'version' => '1.0.0'
        ]);
    });
});

// Health Check Route (for monitoring)
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();
        $database = 'connected';
    } catch (\Exception $e) {
        $database = 'disconnected';
    }
    
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'services' => [
            'database' => $database,
            'cache' => 'connected',
            'storage' => is_writable(storage_path()) ? 'writable' : 'readonly',
            'app_key' => config('app.key') ? 'set' : 'not_set'
        ],
        'system' => [
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'environment' => app()->environment()
        ]
    ]);
})->name('health');

// Debug Routes (only in local environment)
if (app()->environment('local')) {
    Route::get('/debug', function () {
        $info = [
            'php' => [
                'version' => phpversion(),
                'memory_limit' => ini_get('memory_limit'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'laravel' => [
                'version' => app()->version(),
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
            ],
            'database' => [
                'default' => config('database.default'),
                'connections' => array_keys(config('database.connections')),
            ],
            'services' => [
                'cache' => config('cache.default'),
                'queue' => config('queue.default'),
                'session' => config('session.driver'),
                'mail' => config('mail.default'),
            ],
            'routes' => [
                'total' => count(Route::getRoutes()->getRoutes()),
                'middleware_groups' => array_keys(app('router')->getMiddlewareGroups()),
                'route_middleware' => array_keys(app('router')->getMiddleware()),
            ],
            'filesystem' => [
                'default' => config('filesystems.default'),
                'disks' => array_keys(config('filesystems.disks')),
                'storage_writable' => is_writable(storage_path()),
                'bootstrap_writable' => is_writable(base_path('bootstrap/cache')),
            ]
        ];
        
        return view('debug', compact('info'));
    })->name('debug');
    
    Route::get('/phpinfo', function () {
        phpinfo();
    })->name('phpinfo');
    
    Route::get('/routes', function () {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->map(function ($route) {
                return [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'middleware' => implode(', ', $route->middleware()),
                ];
            });
        
        return response()->json($routes);
    })->name('routes.list');
    
    Route::get('/config/{key?}', function ($key = null) {
        if ($key) {
            return response()->json([
                'key' => $key,
                'value' => config($key)
            ]);
        }
        
        return response()->json(config()->all());
    })->name('config.show');
}