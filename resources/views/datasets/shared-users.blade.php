@extends('layouts.app')

@section('title', 'Shared Users: ' . $dataset->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users"></i> Shared Users: {{ $dataset->name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dataset
        </a>
        <a href="{{ route('datasets.share.create', $dataset->id) }}" class="btn btn-primary ms-2">
            <i class="fas fa-share-alt"></i> Share with More Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Users with Access
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Permission</th>
                                <th>Shared By</th>
                                <th>Access Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sharedUsers as $shared)
                            <tr>
                                <td>
                                    @if($shared['permission'] == 'owner')
                                        <i class="fas fa-crown text-warning"></i>
                                    @elseif($shared['permission'] == 'edit')
                                        <i class="fas fa-edit text-success"></i>
                                    @else
                                        <i class="fas fa-eye text-info"></i>
                                    @endif
                                    {{ $shared['user']->name }}
                                    
                                    @if($shared['permission'] == 'owner')
                                        <span class="badge bg-warning ms-2">Owner</span>
                                    @elseif($shared['permission'] == 'edit')
                                        <span class="badge bg-success ms-2">Can Edit</span>
                                    @else
                                        <span class="badge bg-info ms-2">View Only</span>
                                    @endif
                                </td>
                                <td>{{ $shared['user']->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $shared['user']->isAdmin() ? 'danger' : 'primary' }}">
                                        {{ ucfirst($shared['user']->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($shared['permission'] != 'owner')
                                    <select class="form-select form-select-sm permission-select" 
                                            data-dataset-id="{{ $dataset->id }}" 
                                            data-user-id="{{ $shared['user']->id }}"
                                            style="width: auto;">
                                        <option value="view" {{ $shared['permission'] == 'view' ? 'selected' : '' }}>
                                            View Only
                                        </option>
                                        <option value="edit" {{ $shared['permission'] == 'edit' ? 'selected' : '' }}>
                                            Can Edit
                                        </option>
                                    </select>
                                    @else
                                    <span class="text-muted">Owner (full access)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($shared['shared_by'])
                                        {{ $shared['shared_by']->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($shared['permission'] == 'owner')
                                        {{ $dataset->created_at->format('d M Y H:i') }}
                                    @else
                                        @php
                                            $share = $dataset->shares()->where('user_id', $shared['user']->id)->first();
                                        @endphp
                                        @if($share)
                                            {{ $share->created_at->format('d M Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($shared['permission'] != 'owner')
                                    <button class="btn btn-sm btn-danger remove-share"
                                            data-dataset-id="{{ $dataset->id }}"
                                            data-user-id="{{ $shared['user']->id }}"
                                            title="Remove access">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Access Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h1 class="display-4">{{ count($sharedUsers) }}</h1>
                                <p class="mb-0">Total Users with Access</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h1 class="display-4">
                                    {{ $sharedUsers->where('permission', 'edit')->count() }}
                                </h1>
                                <p class="mb-0">Can Edit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h1 class="display-4">
                                    {{ $sharedUsers->where('permission', 'view')->count() }}
                                </h1>
                                <p class="mb-0">View Only</p>
                            </div>
                        </div>
                    </div>
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
                // Update badge
                const $row = $(this).closest('tr');
                const badge = permission === 'edit' ? 'Can Edit' : 'View Only';
                $row.find('.badge').text(badge).removeClass('bg-info bg-success').addClass(permission === 'edit' ? 'bg-success' : 'bg-info');
            }.bind(this),
            error: function(xhr) {
                showError('Failed to update permission');
                // Revert select
                $(this).val($(this).data('previous-value'));
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
                        // Update summary counts
                        updateSummaryCounts();
                    },
                    error: function(xhr) {
                        showError('Failed to remove access');
                    }
                });
            }
        });
    });
    
    function updateSummaryCounts() {
        // This function would update the summary cards
        // You might want to reload the page or make an AJAX call to update counts
        location.reload();
    }
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