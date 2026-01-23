@extends('layouts.app')

@section('title', 'Share Dataset: ' . $dataset->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-share-alt"></i> Share Dataset: {{ $dataset->name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dataset
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus"></i> Share with User
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('datasets.share.store', $dataset->id) }}" method="POST" id="shareForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="user_identifier" class="form-label">
                            <strong>Email or Username</strong>
                        </label>
                        <input type="text" class="form-control @error('user_identifier') is-invalid @enderror" 
                               id="user_identifier" name="user_identifier" 
                               value="{{ old('user_identifier') }}"
                               placeholder="Enter user email or username"
                               required>
                        @error('user_identifier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Enter the email or username of the user you want to share with
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="permission" class="form-label">
                            <strong>Permission Level</strong>
                        </label>
                        <select class="form-select @error('permission') is-invalid @enderror" 
                                id="permission" name="permission" required>
                            <option value="view" {{ old('permission') == 'view' ? 'selected' : '' }}>
                                <i class="fas fa-eye"></i> View Only
                            </option>
                            <option value="edit" {{ old('permission') == 'edit' ? 'selected' : '' }}>
                                <i class="fas fa-edit"></i> Can Edit
                            </option>
                        </select>
                        @error('permission')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <div class="mt-2">
                                <strong><i class="fas fa-eye"></i> View Only:</strong> User can only view the dataset
                            </div>
                            <div>
                                <strong><i class="fas fa-edit"></i> Can Edit:</strong> User can view and edit the dataset
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Information</h6>
                        <ul class="mb-0">
                            <li>The user will receive access to this dataset</li>
                            <li>You can always change or remove access later</li>
                            <li>Dataset owner and admin always have full access</li>
                            <li>Shared users cannot share the dataset further</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-share-square"></i> Share Dataset
                        </button>
                        <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Share Section -->
        @if($shareableUsers->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-users"></i> Quick Share with Existing Users
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('datasets.share.bulk', $dataset->id) }}" method="POST" id="bulkShareForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Select Users</label>
                        <div class="row">
                            @foreach($shareableUsers as $user)
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="users[]" value="{{ $user->email }}" 
                                           id="user_{{ $user->id }}">
                                    <label class="form-check-label" for="user_{{ $user->id }}">
                                        <strong>{{ $user->name }}</strong> <br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bulk_permission" class="form-label">Permission for Selected Users</label>
                        <select class="form-select" id="bulk_permission" name="permission">
                            <option value="view">View Only</option>
                            <option value="edit">Can Edit</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus"></i> Share with Selected Users
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card mt-4">
            <div class="card-body text-center text-muted py-4">
                <i class="fas fa-users fa-3x mb-3"></i>
                <p>No other users available to share with</p>
            </div>
        </div>
        @endif
        
        <!-- Current Shares -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Currently Shared With
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Permission</th>
                                <th>Shared By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <i class="fas fa-crown text-warning"></i> {{ $dataset->user->name }}
                                    <span class="badge bg-warning ms-2">Owner</span>
                                </td>
                                <td>{{ $dataset->user->email }}</td>
                                <td>
                                    <span class="badge bg-success">Full Access</span>
                                </td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            
                            @foreach($dataset->shares as $share)
                            @if($share->user)
                            <tr>
                                <td>{{ $share->user->name }}</td>
                                <td>{{ $share->user->email }}</td>
                                <td>
                                    <select class="form-select form-select-sm permission-select" 
                                            data-dataset-id="{{ $dataset->id }}" 
                                            data-user-id="{{ $share->user->id }}"
                                            style="width: auto;">
                                        <option value="view" {{ $share->permission == 'view' ? 'selected' : '' }}>
                                            View
                                        </option>
                                        <option value="edit" {{ $share->permission == 'edit' ? 'selected' : '' }}>
                                            Edit
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    @if($share->sharer)
                                    <small>{{ $share->sharer->name }}</small>
                                    @else
                                    <small>-</small>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger remove-share"
                                            data-dataset-id="{{ $dataset->id }}"
                                            data-user-id="{{ $share->user->id }}"
                                            title="Remove access">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Change permission
    $('.permission-select').change(function() {
        const datasetId = $(this).data('dataset-id');
        const userId = $(this).data('user-id');
        const permission = $(this).val();
        
        $.ajax({
            url: `/datasets/${datasetId}/shares/${userId}`,
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                permission: permission
            },
            success: function(response) {
                showSuccess('Permission updated successfully!');
            },
            error: function(xhr) {
                showError('Failed to update permission');
            }
        });
    });
    
    // Remove share
    $(document).on('click', '.remove-share', function() {
        const datasetId = $(this).data('dataset-id');
        const userId = $(this).data('user-id');
        const $row = $(this).closest('tr');
        const userName = $row.find('td:first').text().trim();
        
        Swal.fire({
            title: 'Remove Access?',
            html: `Are you sure you want to remove access for <strong>${userName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove access!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/datasets/${datasetId}/shares/${userId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $row.remove();
                        showSuccess('Access removed successfully!');
                    },
                    error: function(xhr) {
                        showError('Failed to remove access');
                    }
                });
            }
        });
    });
    
    // Bulk share form
    $('#bulkShareForm').submit(function(e) {
        const selectedUsers = $('input[name="users[]"]:checked').length;
        
        if (selectedUsers === 0) {
            e.preventDefault();
            showError('Please select at least one user');
            return false;
        }
    });
});

function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'OK'
    });
}
</script>
@endsection