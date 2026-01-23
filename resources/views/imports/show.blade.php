<!-- resources/views/imports/show.blade.php -->
@extends('layouts.app')

@section('title', 'Detail Import')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-info-circle"></i> Detail Import
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('imports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-excel"></i> Informasi Import
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Nama File</th>
                        <td>{{ $import->original_name }}</td>
                    </tr>
                    <tr>
                        <th>Dataset</th>
                        <td>
                            @if($import->dataset)
                                <a href="{{ route('datasets.show', $import->dataset_id) }}" class="text-decoration-none">
                                    <i class="fas fa-database"></i> {{ $import->dataset->name }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'failed' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$import->status] }}">
                                {{ ucfirst($import->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Baris</th>
                        <td>
                            <span class="badge bg-primary">
                                {{ $import->row_count }} baris
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Header</th>
                        <td>
                            @if($import->has_header)
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Baris pertama sebagai header
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-times"></i> Tanpa header
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat</th>
                        <td>{{ $import->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Diperbarui</th>
                        <td>{{ $import->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    @if($import->error_message)
                    <tr>
                        <th>Pesan Error</th>
                        <td class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i> {{ $import->error_message }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs"></i> Aksi
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($import->dataset)
                    <a href="{{ route('datasets.show', $import->dataset_id) }}" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Lihat Dataset
                    </a>
                    @endif
                    
                    @if($import->status === 'failed')
                    <button class="btn btn-warning" onclick="retryImport({{ $import->id }})">
                        <i class="fas fa-redo"></i> Coba Ulang Import
                    </button>
                    @endif
                    
                    <button class="btn btn-danger" onclick="deleteImport({{ $import->id }})">
                        <i class="fas fa-trash"></i> Hapus Riwayat
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Informasi
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb"></i> Tips:</h6>
                    <ul class="mb-0 small">
                        <li>Import yang gagal dapat dicoba ulang</li>
                        <li>Dataset dapat diedit setelah import</li>
                        <li>File asli disimpan di server untuk backup</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@if($import->dataset && $import->dataset->row_count > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table"></i> Preview Data
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                @foreach($import->dataset->columns as $column)
                                    <th>{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $previewRows = $import->dataset->rows()->limit(5)->get();
                            @endphp
                            @foreach($previewRows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                @foreach($import->dataset->columns as $column)
                                    <td>{{ $row->data[$column] ?? '' }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                            @if($import->dataset->row_count > 5)
                            <tr>
                                <td colspan="{{ count($import->dataset->columns) + 1 }}" class="text-center text-muted">
                                    ... dan {{ $import->dataset->row_count - 5 }} baris lainnya
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function retryImport(importId) {
    Swal.fire({
        title: 'Coba Ulang Import?',
        text: 'Import akan diproses kembali',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Coba Ulang',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/imports/' + importId + '/retry',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    Swal.showLoading();
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Gagal mencoba ulang'
                    });
                }
            });
        }
    });
}

function deleteImport(importId) {
    Swal.fire({
        title: 'Hapus Riwayat Import?',
        text: "Data yang dihapus tidak dapat dikembalikan",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/imports/' + importId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.location.href = '{{ route("imports.index") }}';
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal menghapus riwayat import'
                    });
                }
            });
        }
    });
}
</script>
@endsection