@extends('layouts.app')

@section('title', 'Import History')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-history"></i> Riwayat Import
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('imports.create') }}" class="btn btn-primary">
            <i class="fas fa-file-import"></i> Import Baru
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> Daftar Import
        </h5>
    </div>
    <div class="card-body">
        @if($imports->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Nama File</th>
                            <th>Dataset</th>
                            <th>Status</th>
                            <th>Baris</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($imports as $import)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <i class="fas fa-file-excel text-success"></i>
                                {{ $import->original_name }}
                            </td>
                            <td>
                                @if($import->dataset)
                                    <a href="{{ route('datasets.show', $import->dataset_id) }}" class="text-decoration-none">
                                        <i class="fas fa-database"></i> {{ $import->dataset->name }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'failed' => 'danger'
                                    ];
                                    $statusIcons = [
                                        'pending' => 'clock',
                                        'processing' => 'sync',
                                        'completed' => 'check',
                                        'failed' => 'times'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$import->status] ?? 'secondary' }}">
                                    <i class="fas fa-{{ $statusIcons[$import->status] ?? 'question' }}"></i>
                                    {{ ucfirst($import->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $import->row_count }}
                                </span>
                            </td>
                            <td>
                                {{ $import->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('imports.show', $import->id) }}" class="btn btn-outline-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($import->status === 'failed')
                                    <button class="btn btn-outline-warning" onclick="retryImport({{ $import->id }})" title="Coba Ulang">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-danger" onclick="deleteImport({{ $import->id }})" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $imports->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-import fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Belum ada riwayat import</h4>
                <p class="text-muted">Mulai dengan mengimport file Excel/CSV pertama Anda</p>
                <a href="{{ route('imports.create') }}" class="btn btn-primary">
                    <i class="fas fa-file-import"></i> Import File Pertama
                </a>
            </div>
        @endif
    </div>
</div>
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Riwayat import berhasil dihapus',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
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