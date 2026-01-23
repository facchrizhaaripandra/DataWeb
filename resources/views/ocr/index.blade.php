@extends('layouts.app')

@section('title', 'OCR Processing')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">OCR Processing</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('ocr.create') }}" class="btn btn-primary">
            <i class="fas fa-image"></i> New OCR Processing
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">OCR Processing History</h5>
    </div>
    <div class="card-body">
        @if($results->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Dataset</th>
                            <th>Status</th>
                            <th>Detected Rows</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                        <tr>
                            <td>
                                @if(file_exists(storage_path('app/' . $result->image_path)))
                                    <img src="{{ Storage::url($result->image_path) }}" 
                                         alt="OCR Image" 
                                         style="width: 100px; height: 60px; object-fit: cover;">
                                @else
                                    <span class="text-muted">Image not found</span>
                                @endif
                            </td>
                            <td>
                                @if($result->dataset)
                                    <a href="{{ route('datasets.show', $result->dataset_id) }}">
                                        {{ $result->dataset->name }}
                                    </a>
                                @else
                                    <span class="text-muted">No dataset</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processed' => 'success',
                                        'failed' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$result->status] ?? 'secondary' }}">
                                    {{ ucfirst($result->status) }}
                                </span>
                            </td>
                            <td>
                                @if($result->detected_data)
                                    {{ count($result->detected_data) }}
                                @else
                                    0
                                @endif
                            </td>
                            <td>{{ $result->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('ocr.show', $result->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-center">
                    {{ $results->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                <p class="text-muted">No OCR processing records found. Process your first image!</p>
                <a href="{{ route('ocr.create') }}" class="btn btn-primary">
                    <i class="fas fa-image"></i> Process Image
                </a>
            </div>
        @endif
    </div>
</div>
@endsection