@extends('layouts.app')

@section('title', 'Datasets')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Datasets</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('datasets.create') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus"></i> New Dataset
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Datasets</h5>
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="Search datasets..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($datasets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="datasetsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Columns</th>
                                    <th>Rows</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datasets as $dataset)
                                <tr>
                                    <td>
                                        <strong>{{ $dataset->name }}</strong>
                                    </td>
                                    <td>{{ Str::limit($dataset->description, 50) }}</td>
                                    <td>
                                        @if($dataset->columns)
                                            @foreach(array_slice($dataset->columns, 0, 3) as $column)
                                                <span class="badge bg-secondary me-1">{{ $column }}</span>
                                            @endforeach
                                            @if(count($dataset->columns) > 3)
                                                <span class="badge bg-light text-dark">+{{ count($dataset->columns) - 3 }} more</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $dataset->row_count }}</span>
                                    </td>
                                    <td>{{ $dataset->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('datasets.show', $dataset->id) }}" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('datasets.edit', $dataset->id) }}" 
                                               class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger delete-dataset" 
                                                    data-id="{{ $dataset->id }}" 
                                                    data-name="{{ $dataset->name }}" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $datasets->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-table fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">No datasets found</h4>
                        <p class="text-muted">Create your first dataset to start managing data</p>
                        <a href="{{ route('datasets.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First Dataset
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#datasetsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // Delete dataset
    $('.delete-dataset').on('click', function() {
        const datasetId = $(this).data('id');
        const datasetName = $(this).data('name');
        
        Swal.fire({
            title: 'Delete Dataset?',
            html: `Are you sure you want to delete <strong>${datasetName}</strong>?<br>
                   This will delete all data in this dataset.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/datasets/' + datasetId,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'Dataset has been deleted.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Failed to delete dataset.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endsection