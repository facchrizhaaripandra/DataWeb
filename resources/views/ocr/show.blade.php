@extends('layouts.app')

@section('title', 'OCR Result Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">OCR Result Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('ocr.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">OCR Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Image</th>
                        <td>
                            @if(file_exists(storage_path('app/' . $result->image_path)))
                                <img src="{{ Storage::url($result->image_path) }}" 
                                     alt="OCR Image" 
                                     style="max-width: 300px; max-height: 200px;">
                            @else
                                <span class="text-muted">Image not found</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processed' => 'success',
                                    'failed' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$result->status] }}">
                                {{ ucfirst($result->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Dataset</th>
                        <td>
                            @if($result->dataset)
                                <a href="{{ route('datasets.show', $result->dataset_id) }}">
                                    {{ $result->dataset->name }}
                                </a>
                            @else
                                <span class="text-muted">No dataset assigned</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Detected Rows</th>
                        <td>
                            @if($result->detected_data)
                                {{ count($result->detected_data) }} rows detected
                            @else
                                0
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $result->created_at->format('d M Y H:i:s') }}</td>
                    </tr>
                    @if($result->error_message)
                    <tr>
                        <th>Error Message</th>
                        <td class="text-danger">{{ $result->error_message }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                @if($result->status === 'processed' && $result->detected_data)
                <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#saveDataModal">
                    <i class="fas fa-save"></i> Save to Dataset
                </button>
                @endif
                
                @if($result->status === 'failed')
                <button class="btn btn-warning w-100 mb-2" onclick="retryOcr({{ $result->id }})">
                    <i class="fas fa-redo"></i> Retry OCR
                </button>
                @endif
                
                <form action="{{ route('ocr.destroy', $result->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure?')">
                        <i class="fas fa-trash"></i> Delete OCR Record
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($result->detected_data)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Detected Data Preview</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        @for($i = 0; $i < count($result->detected_data[0] ?? []); $i++)
                            <th>Column {{ $i + 1 }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($result->detected_data, 0, 10) as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                    @if(count($result->detected_data) > 10)
                    <tr>
                        <td colspan="{{ count($result->detected_data[0] ?? []) }}" class="text-center text-muted">
                            ... and {{ count($result->detected_data) - 10 }} more rows
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Save Data Modal -->
<div class="modal fade" id="saveDataModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('ocr.save', $result->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Save OCR Data to Dataset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dataset_name" class="form-label">Dataset Name</label>
                        <input type="text" class="form-control" id="dataset_name" 
                               name="dataset_name" value="{{ $result->dataset->name ?? 'OCR Data ' . date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Column Mapping</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Original Column</th>
                                        <th>New Column Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 0; $i < count($result->detected_data[0] ?? []); $i++)
                                    <tr>
                                        <td>Column {{ $i + 1 }}</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" 
                                                   name="columns[{{ $i }}]" 
                                                   value="Field {{ $i + 1 }}">
                                        </td>
                                    </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This will create a new dataset with the detected data.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save to Dataset</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function retryOcr(resultId) {
    Swal.fire({
        title: 'Retry OCR Processing?',
        text: 'This will attempt to process the image again.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, retry',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/ocr/' + resultId + '/retry',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire('Success', 'OCR queued for retry', 'success');
                    setTimeout(() => location.reload(), 2000);
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to retry OCR', 'error');
                }
            });
        }
    });
}
</script>
@endsection