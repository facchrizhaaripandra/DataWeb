@extends('layouts.app')

@section('title', 'System Information')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-server"></i> System Information
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="row">
    <!-- System Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> System Details
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Laravel Version</strong></td>
                                <td>{{ $systemInfo['laravel_version'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version</strong></td>
                                <td>{{ $systemInfo['php_version'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Server Software</strong></td>
                                <td>{{ $systemInfo['server_software'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Database Driver</strong></td>
                                <td>{{ $systemInfo['database_driver'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Timezone</strong></td>
                                <td>{{ $systemInfo['timezone'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Environment</strong></td>
                                <td>
                                    <span class="badge bg-{{ $systemInfo['environment'] == 'production' ? 'success' : 'warning' }}">
                                        {{ $systemInfo['environment'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Storage Path</strong></td>
                                <td><code>{{ $systemInfo['storage_path'] }}</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- PHP Configuration -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog"></i> PHP Configuration
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Upload Max Filesize</strong></td>
                                <td>{{ $systemInfo['upload_max_filesize'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Post Max Size</strong></td>
                                <td>{{ $systemInfo['post_max_size'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Memory Limit</strong></td>
                                <td>{{ $systemInfo['memory_limit'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Max Execution Time</strong></td>
                                <td>{{ ini_get('max_execution_time') }} seconds</td>
                            </tr>
                            <tr>
                                <td><strong>Max Input Time</strong></td>
                                <td>{{ ini_get('max_input_time') }} seconds</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Storage Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-hdd"></i> Storage Information
                </h5>
            </div>
            <div class="card-body">
                @php
                    $total = $storageInfo['total'];
                    $used = $storageInfo['used'];
                    $free = $storageInfo['free'];
                    $usedPercent = $total > 0 ? ($used / $total) * 100 : 0;
                    $freePercent = $total > 0 ? ($free / $total) * 100 : 0;
                @endphp
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Storage Usage</span>
                        <span>{{ number_format($usedPercent, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $usedPercent }}%">
                            {{ number_format($used / (1024*1024*1024), 2) }} GB Used
                        </div>
                        <div class="progress-bar bg-secondary" role="progressbar" 
                             style="width: {{ $freePercent }}%">
                            {{ number_format($free / (1024*1024*1024), 2) }} GB Free
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Total Storage</strong></td>
                                <td>{{ number_format($total / (1024*1024*1024), 2) }} GB</td>
                            </tr>
                            <tr>
                                <td><strong>Used Storage</strong></td>
                                <td>{{ number_format($used / (1024*1024*1024), 2) }} GB</td>
                            </tr>
                            <tr>
                                <td><strong>Free Storage</strong></td>
                                <td>{{ number_format($free / (1024*1024*1024), 2) }} GB</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Database Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-database"></i> Database Information
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><strong>Total Users</strong></td>
                                <td>{{ $databaseInfo['total_users'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Datasets</strong></td>
                                <td>{{ $databaseInfo['total_datasets'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Imports</strong></td>
                                <td>{{ $databaseInfo['total_imports'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total OCR Results</strong></td>
                                <td>{{ $databaseInfo['total_ocr'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Database Size</strong></td>
                                <td>{{ $databaseInfo['database_size'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools"></i> System Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-success mb-2" onclick="createBackup()">
                                <i class="fas fa-save"></i> Create Backup
                            </button>
                            <small class="text-muted">Backup database and files</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-warning mb-2" onclick="clearSystemCache()">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                            <small class="text-muted">Clear all system cache</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-info mb-2" onclick="optimizeDatabase()">
                                <i class="fas fa-database"></i> Optimize DB
                            </button>
                            <small class="text-muted">Optimize database tables</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-danger mb-2" onclick="systemMaintenance()">
                                <i class="fas fa-wrench"></i> Maintenance Mode
                            </button>
                            <small class="text-muted">Enable maintenance mode</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup History -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> Backup History
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshBackupList()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <div id="backupList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading backup history...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function createBackup() {
    Swal.fire({
        title: 'Create System Backup?',
        text: 'This will create a backup of the database and system files.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, create backup!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Creating Backup...',
                text: 'Please wait while the backup is being created.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('/admin/system/backup', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Success!', 'Backup created successfully.', 'success');
                    refreshBackupList();
                } else {
                    Swal.fire('Error!', data.message || 'Failed to create backup.', 'error');
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error!', 'An error occurred.', 'error');
            });
        }
    });
}

function clearSystemCache() {
    Swal.fire({
        title: 'Clear System Cache?',
        text: 'This will clear all cached data including configuration, routes, and views.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, clear cache!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Clearing Cache...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('/admin/system/clear-cache', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Success!', 'System cache cleared successfully.', 'success');
                } else {
                    Swal.fire('Error!', data.message || 'Failed to clear cache.', 'error');
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error!', 'An error occurred.', 'error');
            });
        }
    });
}

function optimizeDatabase() {
    Swal.fire({
        title: 'Optimize Database?',
        text: 'This will optimize all database tables for better performance.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, optimize!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement database optimization
            Swal.fire('Info', 'Database optimization feature coming soon.', 'info');
        }
    });
}

function systemMaintenance() {
    Swal.fire({
        title: 'Maintenance Mode?',
        text: 'This will put the system in maintenance mode. Users will not be able to access the site.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Enable Maintenance'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement maintenance mode
            Swal.fire('Info', 'Maintenance mode feature coming soon.', 'info');
        }
    });
}

function refreshBackupList() {
    const backupList = document.getElementById('backupList');
    backupList.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading backup history...</p>
        </div>
    `;
    
    // Simulate loading backup list
    setTimeout(() => {
        backupList.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                No backups found. Create your first backup.
            </div>
        `;
    }, 1000);
}

// Load backup list on page load
document.addEventListener('DOMContentLoaded', function() {
    refreshBackupList();
});
</script>
@endsection