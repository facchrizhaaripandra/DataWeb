@extends('layouts.app')

@section('title', 'Edit Dataset: ' . $dataset->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Dataset: {{ $dataset->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dataset
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
                <form method="POST" action="{{ route('datasets.update', $dataset->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Dataset Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $dataset->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $dataset->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Access Settings</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                                   {{ old('is_public', $dataset->is_public) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_public">
                                Make this dataset public (anyone can view it)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="access_type" class="form-label">Access Type</label>
                        <select class="form-select @error('access_type') is-invalid @enderror" id="access_type" name="access_type">
                            <option value="private" {{ old('access_type', $dataset->access_type ?? 'private') == 'private' ? 'selected' : '' }}>
                                Private (only you can access)
                            </option>
                            <option value="shared" {{ old('access_type', $dataset->access_type ?? 'private') == 'shared' ? 'selected' : '' }}>
                                Shared (you can share with specific users)
                            </option>
                            <option value="public" {{ old('access_type', $dataset->access_type ?? 'private') == 'public' ? 'selected' : '' }}>
                                Public (anyone can view)
                            </option>
                        </select>
                        @error('access_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('datasets.show', $dataset->id) }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Dataset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dataset Stats</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="h4 mb-0">{{ $dataset->row_count }}</div>
                            <small class="text-muted">Rows</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="h4 mb-0">{{ count($dataset->columns) }}</div>
                            <small class="text-muted">Columns</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted">
                    <strong>Created:</strong><br>
                    {{ $dataset->created_at->format('M j, Y g:i A') }}
                </div>
                <div class="small text-muted mt-2">
                    <strong>Last Updated:</strong><br>
                    {{ $dataset->updated_at->format('M j, Y g:i A') }}
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Tips</h5>
            </div>
            <div class="card-body">
                <ul class="small">
                    <li>Public datasets can be viewed by anyone</li>
                    <li>Shared datasets allow you to control who can access them</li>
                    <li>Private datasets are only accessible by you</li>
                    <li>You can change access settings anytime</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle access type based on public checkbox
    const isPublicCheckbox = document.getElementById('is_public');
    const accessTypeSelect = document.getElementById('access_type');

    function updateAccessType() {
        if (isPublicCheckbox.checked) {
            accessTypeSelect.value = 'public';
        } else if (accessTypeSelect.value === 'public') {
            accessTypeSelect.value = 'private';
        }
    }

    isPublicCheckbox.addEventListener('change', updateAccessType);

    accessTypeSelect.addEventListener('change', function() {
        if (this.value === 'public') {
            isPublicCheckbox.checked = true;
        } else if (this.value === 'private') {
            isPublicCheckbox.checked = false;
        }
    });
});
</script>
@endsection
