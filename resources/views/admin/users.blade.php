@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users"></i> User Management
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Users List</h5>
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" placeholder="Search users..." id="searchUsers">
            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Datasets</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle-sm me-2">
                                        <span class="initials-sm">{{ substr($user->name, 0, 2) }}</span>
                                    </div>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $user->datasets()->count() }}</span>
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">Verified</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger" 
                                            onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @if(!$user->isAdmin())
                                    <button class="btn btn-success" 
                                            onclick="makeAdmin({{ $user->id }}, '{{ $user->name }}')"
                                            title="Make Admin">
                                        <i class="fas fa-user-shield"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">No users found.</p>
            </div>
        @endif
    </div>
</div>

<!-- Add User Modal -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-user-plus"></i> Add New User
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST" id="addUserForm">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="new_name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="new_name" name="name" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="new_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="new_email" name="email" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="new_role" class="form-label">Role</label>
                    <select class="form-select" id="new_role" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="new_password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="new_password" name="password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="new_password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" required>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Add User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
.avatar-circle-sm {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.initials-sm {
    font-size: 0.9rem;
    font-weight: bold;
    color: white;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>
@endsection

@section('scripts')
<script>
function deleteUser(userId, userName) {
    Swal.fire({
        title: 'Delete User?',
        html: `Are you sure you want to delete <strong>${userName}</strong>?<br>
               This will delete all associated data including datasets.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
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
                    Swal.fire('Deleted!', 'User has been deleted.', 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Failed to delete user', 'error');
            });
        }
    });
}

function makeAdmin(userId, userName) {
    Swal.fire({
        title: 'Make Admin?',
        html: `Are you sure you want to make <strong>${userName}</strong> an admin?<br>
               This user will have full access to the system.`,
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

// Search functionality
document.getElementById('searchBtn').addEventListener('click', function() {
    const searchTerm = document.getElementById('searchUsers').value;
    // Implement search logic here
});

// Handle form submission
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', 'User added successfully.', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire('Error', data.message || 'Failed to add user', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'An error occurred', 'error');
    });
});
</script>
@endsection