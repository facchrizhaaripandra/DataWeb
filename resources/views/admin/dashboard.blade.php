@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-cog"></i> Admin Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="{{ route('admin.system') }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-server"></i> System
            </a>
            <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-clipboard-list"></i> Logs
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Users</h5>
                        <h2 class="mb-0">{{ $stats['total_users'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                </div>
                <a href="{{ route('admin.users') }}" class="text-white stretched-link"></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Datasets</h5>
                        <h2 class="mb-0">{{ $stats['total_datasets'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-table fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Imports</h5>
                        <h2 class="mb-0">{{ $stats['total_imports'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-file-import fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-clock"></i> Recent Users
                </h5>
            </div>
            <div class="card-body">
                @if($stats['recent_users']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_users'] as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-muted">No users found.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- System Status -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-heartbeat"></i> System Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-database text-primary"></i> Database</span>
                                <span class="badge bg-success">Connected</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hdd text-success"></i> Storage</span>
                                <span class="badge bg-success">Healthy</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-server text-warning"></i> Cache</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-envelope text-info"></i> Mail Server</span>
                                <span class="badge bg-success">Ready</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-tasks text-danger"></i> Queue</span>
                                <span class="badge bg-success">Running</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-shield-alt text-dark"></i> Security</span>
                                <span class="badge bg-success">Protected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.system') }}" class="btn btn-success">
                        <i class="fas fa-database"></i> System Backup
                    </a>
                    <a href="{{ route('admin.logs') }}" class="btn btn-warning">
                        <i class="fas fa-file-alt"></i> View Logs
                    </a>
                    <a href="{{ route('admin.settings') }}" class="btn btn-info">
                        <i class="fas fa-cogs"></i> System Settings
                    </a>
                    <button class="btn btn-danger" onclick="clearSystemCache()">
                        <i class="fas fa-broom"></i> Clear Cache
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i> Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6>New User Registered</h6>
                            <p class="text-muted small">2 minutes ago</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6>Dataset Imported</h6>
                            <p class="text-muted small">15 minutes ago</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6>OCR Processed</h6>
                            <p class="text-muted small">1 hour ago</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6>System Backup</h6>
                            <p class="text-muted small">3 hours ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endsection

@section('scripts')
<script>
function clearSystemCache() {
    Swal.fire({
        title: 'Clear System Cache?',
        text: 'This will clear all cached data including configuration, routes, and views.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, clear cache!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/admin/system/clear-cache', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'System cache cleared successfully.', 'success');
                } else {
                    Swal.fire('Error!', 'Failed to clear cache.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'An error occurred.', 'error');
            });
        }
    });
}
</script>
@endsection