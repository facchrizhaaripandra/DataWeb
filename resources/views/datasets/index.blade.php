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

<!-- Nav tabs -->
<ul class="nav nav-tabs mb-4" id="datasetTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="owned-tab" type="button" role="tab" aria-controls="owned" aria-selected="true">
            <i class="fas fa-user"></i> My Datasets ({{ $ownedDatasets->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="shared-tab" type="button" role="tab" aria-controls="shared" aria-selected="false">
            <i class="fas fa-share-alt"></i> Shared with Me ({{ $sharedDatasets->count() }})
        </button>
    </li>
</ul>

<!-- Tab content -->
<div class="tab-content" id="datasetTabsContent">
    <!-- Owned Datasets Tab -->
    <div class="tab-pane fade show active" id="owned" role="tabpanel" aria-labelledby="owned-tab">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Datasets</h5>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Search my datasets..." id="searchOwnedInput">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($ownedDatasets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover" id="ownedDatasetsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Owner</th>
                                            <th>Rows</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ownedDatasets as $dataset)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <a href="{{ route('datasets.show', $dataset->id) }}" class="text-decoration-none">
                                                    <strong>{{ $dataset->name }}</strong>
                                                </a>
                                                @if($dataset->user_id === auth()->id())
                                                    <span class="badge bg-warning ms-2">Owner</span>
                                                @elseif($dataset->canEdit())
                                                    <span class="badge bg-success ms-2">Can Edit</span>
                                                @else
                                                    <span class="badge bg-info ms-2">View Only</span>
                                                @endif

                                                @if($dataset->is_public)
                                                    <span class="badge bg-primary ms-1">Public</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($dataset->description, 50) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        @if($dataset->user_id === auth()->id())
                                                            <i class="fas fa-user text-warning"></i>
                                                        @else
                                                            <i class="fas fa-user-share text-info"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div>{{ $dataset->user->name }}</div>
                                                        <small class="text-muted">{{ $dataset->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $dataset->row_count }}</span>
                                                <small class="text-muted">rows</small>
                                            </td>
                                            <td>{{ $dataset->created_at->format('d M Y') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                            @if($dataset->user_id === auth()->id() || auth()->user()->isAdmin())
                                            <a href="{{ route('datasets.edit', $dataset->id) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif

                                                    @if($dataset->user_id === auth()->id() || auth()->user()->isAdmin())
                                                    <form action="{{ route('datasets.destroy', $dataset->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger"
                                                                onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endif

                                                    @if($dataset->user_id === auth()->id())
                                                    <a href="{{ route('datasets.share.create', $dataset->id) }}" class="btn btn-info" title="Share">
                                                        <i class="fas fa-share-alt"></i>
                                                    </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
    </div>

    <!-- Shared Datasets Tab -->
    <div class="tab-pane fade" id="shared" role="tabpanel" aria-labelledby="shared-tab">
        <div class="row mb-4 shared-row">
            <div class="col-md-12 shared-col">
                <div class="card shared-card">
                    <div class="card-header d-flex justify-content-between align-items-center shared-card-header">
                        <h5 class="mb-0">Shared with Me</h5>
                        <div class="input-group shared-input-group" style="width: 300px;">
                            <input type="text" class="form-control shared-search-input" placeholder="Search shared datasets..." id="searchSharedInput">
                            <button class="btn btn-outline-secondary shared-search-btn" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body shared-card-body">
                        @if($sharedDatasets->count() > 0)
                            <div class="table-responsive shared-table-responsive">
                                <table class="table table-hover shared-table" id="sharedDatasetsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Owner</th>
                                            <th>Permission</th>
                                            <th>Rows</th>
                                            <th>Shared Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sharedDatasets as $dataset)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <a href="{{ route('datasets.show', $dataset->id) }}" class="text-decoration-none">
                                                    <strong>{{ $dataset->name }}</strong>
                                                </a>
                                                @if($dataset->shares->where('user_id', auth()->id())->first()->permission === 'edit')
                                                    <span class="badge bg-success ms-2">Can Edit</span>
                                                @else
                                                    <span class="badge bg-info ms-2">View Only</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($dataset->description, 50) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <i class="fas fa-user-share text-info"></i>
                                                    </div>
                                                    <div>
                                                        <div>{{ $dataset->user->name }}</div>
                                                        <small class="text-muted">{{ $dataset->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $share = $dataset->shares->where('user_id', auth()->id())->first();
                                                @endphp
                                                @if($share && $share->permission === 'edit')
                                                    <span class="badge bg-success">Edit</span>
                                                @else
                                                    <span class="badge bg-info">View</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $dataset->row_count }}</span>
                                                <small class="text-muted">rows</small>
                                            </td>
                                            <td>
                                                @if($share)
                                                    {{ $share->created_at->format('d M Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    @if($share && $share->permission === 'edit' && auth()->user()->isAdmin())
                                                    <a href="{{ route('datasets.edit', $dataset->id) }}" class="btn btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-share-alt fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">No shared datasets</h4>
                                <p class="text-muted">Datasets shared with you will appear here</p>
                            </div>
                        @endif
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
    console.log('Datasets page loaded');
    console.log('Owned datasets count:', {{ $ownedDatasets->count() }});
    console.log('Shared datasets count:', {{ $sharedDatasets->count() }});





    // Search functionality for owned datasets
    $('#searchOwnedInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#ownedDatasetsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Search functionality for shared datasets
    $('#searchSharedInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#sharedDatasetsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Tab functionality
    $('#shared-tab').on('click', function() {
        $('.tab-pane').removeClass('show active');
        $('.nav-link').removeClass('active');
        $('#shared').addClass('show active');
        $(this).addClass('active');
    });

    $('#owned-tab').on('click', function() {
        $('.tab-pane').removeClass('show active');
        $('.nav-link').removeClass('active');
        $('#owned').addClass('show active');
        $(this).addClass('active');
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
