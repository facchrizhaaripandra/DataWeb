@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Datasets</h5>
                        <h2 class="mb-0">{{ $stats['total_datasets'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-table fa-3x"></i>
                    </div>
                </div>
                <a href="{{ route('datasets.index') }}" class="text-white stretched-link"></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Import</h5>
                        <h2 class="mb-0">{{ $stats['total_imports'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-file-import fa-3x"></i>
                    </div>
                </div>
                <a href="{{ route('imports.index') }}" class="text-white stretched-link"></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">OCR Processing</h5>
                        <h2 class="mb-0">{{ $stats['total_ocr'] }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-image fa-3x"></i>
                    </div>
                </div>
                <a href="{{ route('ocr.index') }}" class="text-dark stretched-link"></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Data</h5>
                        <h2 class="mb-0">
                            @php
                                $total = 0;
                                foreach($stats['recent_datasets'] as $dataset) {
                                    $total += $dataset->row_count;
                                }
                                echo $total;
                            @endphp
                        </h2>
                    </div>
                    <div>
                        <i class="fas fa-chart-line fa-3x"></i>
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
                <h5 class="mb-0">Recent Datasets</h5>
            </div>
            <div class="card-body">
                @if($stats['recent_datasets']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Rows</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_datasets'] as $dataset)
                                <tr>
                                    <td>{{ $dataset->name }}</td>
                                    <td>{{ Str::limit($dataset->description, 50) }}</td>
                                    <td><span class="badge bg-primary">{{ $dataset->row_count }}</span></td>
                                    <td>{{ $dataset->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-table fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No datasets found. Create your first dataset!</p>
                        <a href="{{ route('datasets.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Dataset
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('datasets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Dataset
                    </a>
                    <a href="{{ route('imports.create') }}" class="btn btn-success">
                        <i class="fas fa-file-import"></i> Import from Excel
                    </a>
                    <a href="{{ route('ocr.create') }}" class="btn btn-warning">
                        <i class="fas fa-camera"></i> OCR from Image
                    </a>
                    <a href="{{ route('dashboard.analytics') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> View Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection