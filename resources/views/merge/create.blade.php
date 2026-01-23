@extends('layouts.app')

@section('title', 'Merge Datasets')

@section('styles')
<style>
    .dataset-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    .dataset-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .dataset-card.selected {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .merge-preview {
        max-height: 400px;
        overflow: auto;
    }
    .column-badge {
        font-size: 0.75em;
        margin-right: 3px;
        margin-bottom: 3px;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-object-group"></i> Merge Datasets
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <form id="mergeForm" action="{{ route('merge.store') }}" method="POST">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> Merge Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>Select First Dataset</strong>
                                </label>
                                <div id="dataset1List" class="row">
                                    @foreach($datasets as $dataset)
                                    <div class="col-md-6 mb-3">
                                        <div class="card dataset-card" 
                                             data-dataset-id="{{ $dataset->id }}"
                                             onclick="selectDataset(1, {{ $dataset->id }})">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="fas fa-database"></i> {{ $dataset->name }}
                                                </h6>
                                                <div class="mb-2">
                                                    <span class="badge bg-primary">{{ $dataset->row_count }} rows</span>
                                                    <span class="badge bg-secondary">{{ count($dataset->columns) }} columns</span>
                                                </div>
                                                <div class="small text-muted">
                                                    @foreach(array_slice($dataset->columns, 0, 3) as $column)
                                                        <span class="badge bg-light text-dark column-badge">{{ $column }}</span>
                                                    @endforeach
                                                    @if(count($dataset->columns) > 3)
                                                        <span class="badge bg-light text-dark column-badge">+{{ count($dataset->columns) - 3 }} more</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="dataset1_id" id="dataset1_id" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>Select Second Dataset</strong>
                                </label>
                                <div id="dataset2List" class="row">
                                    @foreach($datasets as $dataset)
                                    <div class="col-md-6 mb-3">
                                        <div class="card dataset-card" 
                                             data-dataset-id="{{ $dataset->id }}"
                                             onclick="selectDataset(2, {{ $dataset->id }})">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="fas fa-database"></i> {{ $dataset->name }}
                                                </h6>
                                                <div class="mb-2">
                                                    <span class="badge bg-primary">{{ $dataset->row_count }} rows</span>
                                                    <span class="badge bg-secondary">{{ count($dataset->columns) }} columns</span>
                                                </div>
                                                <div class="small text-muted">
                                                    @foreach(array_slice($dataset->columns, 0, 3) as $column)
                                                        <span class="badge bg-light text-dark column-badge">{{ $column }}</span>
                                                    @endforeach
                                                    @if(count($dataset->columns) > 3)
                                                        <span class="badge bg-light text-dark column-badge">+{{ count($dataset->columns) - 3 }} more</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="dataset2_id" id="dataset2_id" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="merge_type" class="form-label">
                                    <strong>Merge Type</strong>
                                </label>
                                <select class="form-select" id="merge_type" name="merge_type" required onchange="updatePreview()">
                                    <option value="">-- Select Merge Type --</option>
                                    <option value="union">Union (Combine Rows)</option>
                                    <option value="join">Join (Combine Columns)</option>
                                    <option value="concatenate">Concatenate (Horizontal Merge)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="new_dataset_name" class="form-label">
                                    <strong>New Dataset Name</strong>
                                </label>
                                <input type="text" class="form-control" 
                                       id="new_dataset_name" name="new_dataset_name" 
                                       placeholder="Enter name for merged dataset" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4" id="previewCard" style="display: none;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-eye"></i> Merge Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="previewContent">
                        <!-- Preview will be loaded here -->
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-object-group"></i> Merge Datasets
                        </button>
                        <a href="{{ route('merge.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedDataset1 = null;
let selectedDataset2 = null;

function selectDataset(number, datasetId) {
    // Reset selection
    $(`.dataset-card[data-dataset-id="${selectedDataset1}"]`).removeClass('selected');
    $(`.dataset-card[data-dataset-id="${selectedDataset2}"]`).removeClass('selected');
    
    // Set new selection
    if (number === 1) {
        selectedDataset1 = datasetId;
        $('#dataset1_id').val(datasetId);
        $(`.dataset-card[data-dataset-id="${datasetId}"]`).addClass('selected');
    } else {
        selectedDataset2 = datasetId;
        $('#dataset2_id').val(datasetId);
        $(`.dataset-card[data-dataset-id="${datasetId}"]`).addClass('selected');
    }
    
    // Enable/disable submit button
    updateSubmitButton();
    updatePreview();
}

function updateSubmitButton() {
    const isValid = selectedDataset1 && selectedDataset2 && 
                   $('#merge_type').val() && 
                   $('#new_dataset_name').val().trim();
    
    $('#submitBtn').prop('disabled', !isValid);
}

function updatePreview() {
    if (!selectedDataset1 || !selectedDataset2 || !$('#merge_type').val()) {
        $('#previewCard').hide();
        return;
    }
    
    $('#previewCard').show();
    $('#previewContent').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Generating preview...</p>
        </div>
    `);
    
    const formData = {
        dataset1_id: selectedDataset1,
        dataset2_id: selectedDataset2,
        merge_type: $('#merge_type').val(),
        _token: '{{ csrf_token() }}'
    };
    
    $.ajax({
        url: '{{ route("merge.preview") }}',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                displayPreview(response.preview);
            } else {
                $('#previewContent').html(`
                    <div class="alert alert-danger">
                        Failed to generate preview
                    </div>
                `);
            }
        },
        error: function() {
            $('#previewContent').html(`
                <div class="alert alert-danger">
                    Error loading preview
                </div>
            `);
        }
    });
}

function displayPreview(preview) {
    let html = `
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Dataset 1: ${preview.dataset1.name}</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Columns:</strong> ${preview.dataset1.columns.length}</p>
                        <p><strong>Rows:</strong> ${preview.dataset1.row_count}</p>
                        <div class="small text-muted">
                            ${preview.dataset1.columns.slice(0, 5).map(col => 
                                `<span class="badge bg-light text-dark column-badge">${col}</span>`
                            ).join('')}
                            ${preview.dataset1.columns.length > 5 ? 
                                `<span class="badge bg-light text-dark column-badge">+${preview.dataset1.columns.length - 5} more</span>` : 
                                ''}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Dataset 2: ${preview.dataset2.name}</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Columns:</strong> ${preview.dataset2.columns.length}</p>
                        <p><strong>Rows:</strong> ${preview.dataset2.row_count}</p>
                        <div class="small text-muted">
                            ${preview.dataset2.columns.slice(0, 5).map(col => 
                                `<span class="badge bg-light text-dark column-badge">${col}</span>`
                            ).join('')}
                            ${preview.dataset2.columns.length > 5 ? 
                                `<span class="badge bg-light text-dark column-badge">+${preview.dataset2.columns.length - 5} more</span>` : 
                                ''}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Merged Result</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Merge Type:</strong> <span class="badge bg-info">${preview.merge_type}</span></p>
                        <p><strong>Result Columns:</strong> ${preview.result_columns.length}</p>
                        <div class="merge-preview mt-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            ${preview.result_columns.map(col => 
                                                `<th>${col}</th>`
                                            ).join('')}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            ${preview.result_columns.map(() => 
                                                `<td class="text-muted">Sample data...</td>`
                                            ).join('')}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> This is a preview of the merged dataset structure.
            Actual data will be generated after merging.
        </div>
    `;
    
    $('#previewContent').html(html);
}

// Form validation
$('#merge_type, #new_dataset_name').on('change keyup', function() {
    updateSubmitButton();
    updatePreview();
});

// Handle form submission
$('#mergeForm').submit(function(e) {
    if (!selectedDataset1 || !selectedDataset2) {
        e.preventDefault();
        alert('Please select both datasets');
        return;
    }
    
    $('#submitBtn').html('<i class="fas fa-spinner fa-spin"></i> Merging...');
    $('#submitBtn').prop('disabled', true);
});
</script>
@endsection