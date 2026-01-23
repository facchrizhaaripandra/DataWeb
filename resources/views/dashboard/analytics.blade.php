@extends('layouts.app')

@section('title', 'Analytics')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Data Analytics</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dataset Creation Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="datasetChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Import Status</h5>
            </div>
            <div class="card-body">
                <canvas id="importChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Analytics Recommendations</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <i class="fas fa-lightbulb"></i> Data Quality Check
                                </h5>
                                <p class="card-text">
                                    Check for missing values and inconsistencies in your datasets.
                                </p>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    Run Quality Check
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success">
                                    <i class="fas fa-chart-line"></i> Trend Analysis
                                </h5>
                                <p class="card-text">
                                    Identify patterns and trends in your data over time.
                                </p>
                                <a href="#" class="btn btn-outline-success btn-sm">
                                    Analyze Trends
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body">
                                <h5 class="card-title text-info">
                                    <i class="fas fa-project-diagram"></i> Data Correlation
                                </h5>
                                <p class="card-text">
                                    Discover relationships between different data columns.
                                </p>
                                <a href="#" class="btn btn-outline-info btn-sm">
                                    Find Correlations
                                </a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Dataset Chart
    const datasetCtx = document.getElementById('datasetChart').getContext('2d');
    const datasetChart = new Chart(datasetCtx, {
        type: 'line',
        data: {
            labels: @json($datasetStats->pluck('date')),
            datasets: [{
                label: 'Datasets Created',
                data: @json($datasetStats->pluck('count')),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
    
    // Import Chart
    const importCtx = document.getElementById('importChart').getContext('2d');
    const importChart = new Chart(importCtx, {
        type: 'doughnut',
        data: {
            labels: @json($importStats->pluck('status')),
            datasets: [{
                data: @json($importStats->pluck('count')),
                backgroundColor: [
                    '#ffc107', // pending
                    '#17a2b8', // processing
                    '#28a745', // completed
                    '#dc3545'  // failed
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endsection