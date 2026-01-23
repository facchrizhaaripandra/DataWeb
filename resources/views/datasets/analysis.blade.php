@extends('layouts.app')

@section('title', 'Dataset Analysis: ' . $dataset->name)

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dataset Analysis: {{ $dataset->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dataset
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Summary Statistics</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total Rows:</strong></td>
                        <td>{{ $analysis['total_rows'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Columns:</strong></td>
                        <td>{{ count($dataset->columns) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $dataset->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td>{{ $dataset->updated_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Column Statistics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Column Name</th>
                                <th>Non-empty Values</th>
                                <th>Unique Values</th>
                                <th>Empty Values</th>
                                <th>Completeness</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analysis['column_stats'] as $column => $stats)
                            <tr>
                                <td><strong>{{ $column }}</strong></td>
                                <td>{{ $stats['count'] }}</td>
                                <td>{{ $stats['unique'] }}</td>
                                <td>{{ $stats['empty'] }}</td>
                                <td>
                                    @php
                                        $completeness = ($stats['count'] / $analysis['total_rows']) * 100;
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $completeness > 90 ? 'success' : ($completeness > 70 ? 'warning' : 'danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ $completeness }}%">
                                            {{ round($completeness, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Data Insights & Recommendations</h5>
            </div>
            <div class="card-body">
                @php
                    $totalEmpty = 0;
                    foreach($analysis['column_stats'] as $stats) {
                        $totalEmpty += $stats['empty'];
                    }
                @endphp
                
                @if($totalEmpty > 0)
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Data Quality Issues</h5>
                    <p>
                        Your dataset has <strong>{{ $totalEmpty }}</strong> empty values across all columns.
                        Consider cleaning your data before analysis.
                    </p>
                    <button class="btn btn-sm btn-outline-warning">Clean Data</button>
                </div>
                @endif
                
                @if(count($dataset->columns) > 5)
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Data Structure</h5>
                    <p>
                        Your dataset has <strong>{{ count($dataset->columns) }}</strong> columns. 
                        This is suitable for multidimensional analysis.
                    </p>
                </div>
                @endif
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Quick Analysis Actions</h5>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-search"></i> Find Duplicates
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-filter"></i> Filter Outliers
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <i class="fas fa-calculator"></i> Calculate Statistics
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Export Options</h5>
                        <div class="d-grid gap-2">
                            <a href="{{ route('datasets.export', $dataset->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </a>
                            <button class="btn btn-outline-success">
                                <i class="fas fa-chart-bar"></i> Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection