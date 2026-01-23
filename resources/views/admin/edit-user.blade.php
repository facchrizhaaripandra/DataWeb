@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit"></i> Edit User: {{ $user->name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" 
                                id="role" name="role" required>
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="email_verified" 
                                   name="email_verified" value="1" 
                                   {{ $user->email_verified_at ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_verified">
                                Email Verified
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Changing the role will affect user permissions immediately.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Change Password Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.updatePassword', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Only change password if user requested it.
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- User Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Statistics</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-circle mb-3">
                        <span class="initials">{{ substr($user->name, 0, 2) }}</span>
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-id-badge"></i> User ID</span>
                        <span class="fw-bold">#{{ $user->id }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-table"></i> Datasets</span>
                        <span class="badge bg-primary rounded-pill">{{ $user->datasets()->count() }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-file-import"></i> Imports</span>
                        <span class="badge bg-success rounded-pill">{{ $user->imports()->count() }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-image"></i> OCR Results</span>
                        <span class="badge bg-info rounded-pill">{{ $user->ocrResults()->count() }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar"></i> Joined</span>
                        <span class="text-muted">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clock"></i> Last Active</span>
                        <span class="text-muted">{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    These actions are irreversible. Please proceed with caution.
                </p>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger" 
                            onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                        <i class="fas fa-trash"></i> Delete User Account
                    </button>
                    
                    @if(!$user->isAdmin())
                    <button class="btn btn-outline-warning" 
                            onclick="makeAdmin({{ $user->id }}, '{{ $user->name }}')">
                        <i class="fas fa-user-shield"></i> Make Admin
                    </button>
                    @elseif($user->id != auth()->id())
                    <button class="btn btn-outline-warning" 
                            onclick="removeAdmin({{ $user->id }}, '{{ $user->name }}')">
                        <i class="fas fa-user-minus"></i> Remove Admin
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.initials {
    font-size: 2rem;
    font-weight: bold;
    color: white;
}
</style>
@endsection

@section('scripts')
<script>
function deleteUser(userId, userName) {
    Swal.fire({
        title: 'Delete User Account?',
        html: `Are you sure you want to delete <strong>${userName}</strong> permanently?<br>
               <span class="text-danger">This action cannot be undone!</span>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete permanently!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'User account has been deleted.', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.users") }}';
                    }, 1500);
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to delete user account', 'error');
            });
        }
    });
}

function makeAdmin(userId, userName) {
    Swal.fire({
        title: 'Make Admin?',
        html: `Are you sure you want to make <strong>${userName}</strong> an admin?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, make admin!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/users/${userId}/make-admin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'User is now an admin.', 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to update user role', 'error');
            });
        }
    });
}

function removeAdmin(userId, userName) {
    Swal.fire({
        title: 'Remove Admin?',
        html: `Are you sure you want to remove admin privileges from <strong>${userName}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, remove admin!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/users/${userId}/remove-admin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'Admin privileges removed.', 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to update user role', 'error');
            });
        }
    });
}
</script>
@endsection