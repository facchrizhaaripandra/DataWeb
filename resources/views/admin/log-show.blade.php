@extends('layouts.app')

@section('title', 'Log Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-clipboard-list"></i> Log Details
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Logs
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Log Entry #{{ $logId }}</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            This is a placeholder for log details. In a real application, 
            you would fetch and display specific log entry details here.
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th width="20%">Log ID</th>
                        <td>{{ $logId }}</td>
                    </tr>
                    <tr>
                        <th>Timestamp</th>
                        <td>{{ now()->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td>
                            <span class="badge bg-info">INFO</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Message</th>
                        <td>Sample log message for demonstration purposes.</td>
                    </tr>
                    <tr>
                        <th>Context</th>
                        <td>
                            <pre class="bg-light p-3"><code>{
    "user_id": 1,
    "action": "view_log",
    "ip_address": "127.0.0.1"
}</code></pre>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection