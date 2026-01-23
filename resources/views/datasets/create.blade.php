@extends('layouts.app')

@section('title', 'Create Dataset')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create New Dataset</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('datasets.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Datasets
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dataset Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('datasets.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Dataset Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">Dataset Columns *</label>
                        <div id="columns-container">
                            @if(old('columns'))
                                @foreach(old('columns') as $index => $column)
                                    <div class="input-group mb-2 column-row">
                                        <input type="text" class="form-control" name="columns[]" 
                                               value="{{ $column }}" required>
                                        @if($index > 0)
                                            <button type="button" class="btn btn-outline-danger remove-column">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2 column-row">
                                    <input type="text" class="form-control" name="columns[]" 
                                           placeholder="Column name" required>
                                </div>
                                <div class="input-group mb-2 column-row">
                                    <input type="text" class="form-control" name="columns[]" 
                                           placeholder="Column name" required>
                                    <button type="button" class="btn btn-outline-danger remove-column">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        <button type="button" id="add-column" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-plus"></i> Add Column
                        </button>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('datasets.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Dataset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tips</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Give your dataset a descriptive name</li>
                    <li>Add columns that represent your data structure</li>
                    <li>You can always add more columns later</li>
                    <li>Example columns: Name, Email, Age, etc.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add column
    document.getElementById('add-column').addEventListener('click', function() {
        const container = document.getElementById('columns-container');
        const div = document.createElement('div');
        div.className = 'input-group mb-2 column-row';
        div.innerHTML = `
            <input type="text" class="form-control" name="columns[]" placeholder="Column name" required>
            <button type="button" class="btn btn-outline-danger remove-column">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    });
    
    // Remove column
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-column')) {
            const row = e.target.closest('.column-row');
            if (row && document.querySelectorAll('.column-row').length > 1) {
                row.remove();
            }
        }
    });
});
</script>
@endsection