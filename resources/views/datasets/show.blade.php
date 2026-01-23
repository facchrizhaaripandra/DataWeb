@extends('layouts.app')

@section('title', $dataset->name)

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
<style>
    /* Main table wrapper */
    .table-wrapper {
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: white;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    
    /* Scroll container */
    .table-scroll-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        position: relative;
    }

    .table-dark{
        color: black;
    }
    
    /* Custom scrollbar */
    .table-scroll-container::-webkit-scrollbar {
        height: 10px;
        width: 10px;
    }
    
    .table-scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
    }
    
    .table-scroll-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 5px;
        border: 2px solid #f1f1f1;
    }
    
    .table-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .table-scroll-fix {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: visible;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .table-scroll-fix table {
        min-width: 100% !important;
        max-width: 100% !important;
        margin-bottom: 0 !important;
    }

    /* Untuk form yang panjang */
    .form-scroll-fix {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    /* Untuk card content */
    .card-scroll-fix {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    /* Responsive grid fix */
    .row-fix {
        margin-left: 0 !important;
        margin-right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .col-fix {
        padding-left: 0 !important;
        padding-right: 0 !important;
        max-width: 100% !important;
    }
    
    /* Fixed columns */
    .fixed-column {
        position: sticky !important;
        left: 0;
        background: white;
        z-index: 10;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        min-width: 50px !important;
        max-width: 50px !important;
    }
    
    .fixed-index-column {
        position: sticky !important;
        left: 50px;
        background: white;
        z-index: 10;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        min-width: 70px !important;
        max-width: 70px !important;
    }
    
    .fixed-action-column {
        position: sticky !important;
        right: 0;
        background: white;
        z-index: 10;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        min-width: 100px !important;
        max-width: 100px !important;
    }
    
    /* Column headers */
    .column-header {
        position: relative;
        min-width: 150px;
        background: #000000 !important;
    }
    
    .column-header:hover {
        background-color: #e9ecef !important;
    }
    
    .column-actions {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0;
        transition: opacity 0.2s;
        display: flex;
        gap: 2px;
    }
    
    .column-header:hover .column-actions {
        opacity: 1;
    }
    
    .column-name {
        display: inline-block;
        max-width: calc(100% - 40px);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    /* Scroll indicator */
    .scroll-indicator {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 20;
        background: rgba(255, 255, 255, 0.9);
        padding: 5px;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .table-wrapper:hover .scroll-indicator {
        opacity: 1;
    }
    
    .scroll-indicator i {
        color: #6c757d;
        font-size: 1.5rem;
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {transform: translateX(0);}
        40% {transform: translateX(-5px);}
        60% {transform: translateX(-3px);}
    }
    
    /* Sticky header */
    .sticky-header {
        position: sticky;
        top: 0;
        background: white;
        z-index: 100;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    /* Table styling */
    .data-table {
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    
    .data-table th {
        position: sticky;
        top: 0;
        background: #f8f9fa !important;
        z-index: 5;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6 !important;
    }
    
    .data-table td {
        vertical-align: middle !important;
        border-bottom: 1px solid #dee2e6 !important;
    }
    
    .data-table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05) !important;
    }
    
    .data-table tbody tr.selected {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    
    /* Editable cells */
    .editable-cell {
        cursor: pointer;
        transition: background-color 0.2s;
        position: relative;
        min-width: 150px;
        max-width: 300px;
    }
    
    .editable-cell:hover {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    
    .editable-cell.editing {
        background-color: rgba(255, 193, 7, 0.2) !important;
        padding: 0 !important;
    }
    
    .cell-content {
        display: block;
        padding: 0.5rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .edit-input-group {
        margin: 0;
        min-width: 100%;
    }
    
    .edit-input {
        border: none;
        border-radius: 0;
        padding: 0.5rem;
        background: transparent;
        width: 100%;
    }
    
    /* Badges */
    .column-badge {
        font-size: 0.7em;
        padding: 2px 8px;
        margin-left: 8px;
    }
    
    .row-count-badge {
        font-size: 0.9em;
        padding: 5px 12px;
    }
    
    /* Action buttons */
    .btn-column-action {
        padding: 2px 6px;
        font-size: 0.8rem;
    }
    
    .btn-row-action {
        padding: 2px 8px;
        margin: 0 1px;
    }
    
    /* Empty state */
    .empty-state {
        padding: 3rem 1rem;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        opacity: 0.3;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        border-radius: 8px;
    }
    
    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .column-header {
            min-width: 120px;
        }
        
        .editable-cell {
            min-width: 120px;
        }
        
        .fixed-column {
            min-width: 40px !important;
            max-width: 40px !important;
        }
        
        .fixed-index-column {
            min-width: 50px !important;
            max-width: 50px !important;
            left: 40px;
        }
        
        .fixed-action-column {
            min-width: 80px !important;
            max-width: 80px !important;
        }
    }
    
    /* Hover scroll effect */
    .scroll-hint-left, .scroll-hint-right {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 30px;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        pointer-events: none;
    }
    
    .scroll-hint-left {
        left: 0;
        background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
    }
    
    .scroll-hint-right {
        right: 0;
        background: linear-gradient(to left, rgba(0,0,0,0.1), transparent);
    }
    
    .table-scroll-container:hover .scroll-hint-left,
    .table-scroll-container:hover .scroll-hint-right {
        opacity: 1;
    }
    
    .scroll-hint-left i, .scroll-hint-right i {
        color: #6c757d;
        font-size: 1.2rem;
    }
    
    /* Column resize handles */
    .column-resize-handle {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        height: 100%;
        cursor: col-resize;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .column-header:hover .column-resize-handle {
        opacity: 0.5;
    }
    
    .column-resize-handle:hover {
        opacity: 1 !important;
        background-color: #3498db;
    }

    .dataset-table-container {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: visible;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 20px;
        background: white;
    }

    .dataset-table-container table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: auto;
        margin-bottom: 0 !important;
    }

    .dataset-table-container th {
        background: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
        min-width: 150px;
    }

    .dataset-table-container td {
        min-width: 150px;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Fixed column untuk nomor dan aksi */
    .fixed-left-column {
        position: sticky;
        left: 0;
        background: white;
        z-index: 20;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

    .fixed-right-column {
        position: sticky;
        right: 0;
        background: white;
        z-index: 20;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-table"></i> {{ $dataset->name }}
        @if($dataset->user_id === auth()->id())
            <span class="badge bg-warning">Owner</span>
        @elseif($canEdit)
            <span class="badge bg-success">Can Edit</span>
        @else
            <span class="badge bg-info">View Only</span>
        @endif
        
        @if($dataset->is_public)
            <span class="badge bg-primary">Public</span>
        @endif
        
        @if($dataset->description)
            <small class="text-muted fs-6">- {{ $dataset->description }}</small>
        @endif
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('datasets.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            
            @if($dataset->canEditDataset())
            <a href="{{ route('datasets.edit', $dataset->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i> Edit Dataset
            </a>
            @endif
            
            @if($dataset->user_id === auth()->id())
            <a href="{{ route('datasets.share.create', $dataset->id) }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-share-alt"></i> Share
            </a>
            @endif
            
            <a href="{{ route('datasets.analyze', $dataset->id) }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-chart-bar"></i> Analyze
            </a>
            <button class="btn btn-sm btn-outline-success" onclick="exportData()">
                <i class="fas fa-file-export"></i> Export
            </button>
        </div>
    </div>
</div>

<!-- Dataset Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Baris</h6>
                        <h3 class="mb-0">{{ $dataset->row_count }}</h3>
                    </div>
                    <div>
                        <i class="fas fa-list-ol fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Total Kolom</h6>
                        <h3 class="mb-0">{{ count($dataset->columns) }}</h3>
                    </div>
                    <div>
                        <i class="fas fa-columns fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Dibuat</h6>
                        <h6 class="mb-0">{{ $dataset->created_at->format('d M Y H:i') }}</h6>
                    </div>
                    <div>
                        <i class="fas fa-calendar-plus fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1">Terakhir Diubah</h6>
                        <h6 class="mb-0">{{ $dataset->updated_at->format('d M Y H:i') }}</h6>
                    </div>
                    <div>
                        <i class="fas fa-history fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center sticky-header">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="fas fa-database me-2"></i> Data Table 
            <span class="badge bg-primary row-count-badge ms-2">{{ $dataset->row_count }} baris</span>
            <span class="badge bg-success column-badge">{{ count($dataset->columns) }} kolom</span>
        </h5>
        <div class="btn-group">
            @if($canEdit)
            <button class="btn btn-sm btn-primary" id="addRowBtn">
                <i class="fas fa-plus me-1"></i> Tambah Baris
            </button>
            <button class="btn btn-sm btn-success" onclick="showAddColumnModal()">
                <i class="fas fa-columns me-1"></i> Tambah Kolom
            </button>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> Import Excel
            </button>
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#ocrModal">
                <i class="fas fa-image me-1"></i> OCR dari Foto
            </button>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#manageColumnsModal">
                <i class="fas fa-cog me-1"></i> Kelola Kolom
            </button>
            @endif
        </div>
    </div>
    
    <div class="card-body p-0 position-relative">
        @if($dataset->row_count > 0)
        <div id="tableLoading" class="loading-overlay" style="display: none;">
            <div class="loading-spinner"></div>
        </div>
        
        <div class="table-wrapper">
            <div class="scroll-hint-left">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="scroll-hint-right">
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="scroll-indicator">
                <i class="fas fa-chevron-right"></i>
            </div>
            
            <div class="table-scroll-container">
                <table class="table data-table table-hover mb-0" id="dataTable">
                    <thead class="table-dark">
                        <tr>
                            <!-- Checkbox column -->
                            <th class="fixed-column text-center">
                                <input type="checkbox" id="selectAllRows" class="form-check-input">
                            </th>
                            
                            <!-- Index column -->
                            <th class="fixed-index-column text-center">
                                #
                            </th>
                            
                            <!-- Data columns -->
                            @foreach($dataset->columns as $column)
                                <th class="column-header" data-column="{{ $column }}">
                                    <div class="d-flex justify-content-between align-items-center position-relative">
                                        <span class="column-name" title="{{ $column }}">
                                            {{ $column }}
                                        </span>
                                        <div class="column-actions">
                                            <button class="btn btn-xs btn-outline-warning btn-column-action" 
                                                    onclick="renameColumn('{{ $column }}')"
                                                    title="Ubah nama kolom">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-xs btn-outline-danger btn-column-action" 
                                                    onclick="deleteColumn('{{ $column }}')"
                                                    title="Hapus kolom">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="column-resize-handle"></div>
                                    </div>
                                </th>
                            @endforeach
                            
                            @if($canEdit)
                            <!-- Action column -->
                            <th class="fixed-action-column text-center">
                                Aksi
                            </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $index => $row)
                        <tr id="row-{{ $row->id }}" data-row-id="{{ $row->id }}" class="data-row">
                            <!-- Checkbox cell -->
                            <td class="fixed-column text-center">
                                <input type="checkbox" class="form-check-input row-checkbox" 
                                       data-row-id="{{ $row->id }}">
                            </td>
                            
                            <!-- Index cell -->
                            <td class="fixed-index-column text-center fw-bold">
                                {{ ($rows->currentPage() - 1) * $rows->perPage() + $index + 1 }}
                            </td>
                            
                            <!-- Data cells -->
                            @foreach($dataset->columns as $column)
                                <td class="editable-cell" 
                                    data-column="{{ $column }}" 
                                    data-row-id="{{ $row->id }}"
                                    data-original-value="{{ $row->data[$column] ?? '' }}">
                                    <div class="cell-content" title="{{ $row->data[$column] ?? '' }}">
                                        {{ $row->data[$column] ?? '' }}
                                    </div>
                                </td>
                            @endforeach
                            
                            @if($canEdit)
                            <!-- Action cell -->
                            <td class="fixed-action-column">
                                <div class="btn-group btn-group-sm d-flex justify-content-center">
                                    <button class="btn btn-outline-secondary btn-row-action edit-row"
                                            data-row-id="{{ $row->id }}"
                                            title="Edit baris lengkap">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-row-action delete-row"
                                            data-row-id="{{ $row->id }}"
                                            title="Hapus baris">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="btn-group me-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="selectAllRows()">
                            <i class="fas fa-check-square me-1"></i> Pilih Semua
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteSelectedRows()">
                            <i class="fas fa-trash me-1"></i> Hapus Terpilih
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="duplicateSelectedRows()">
                            <i class="fas fa-copy me-1"></i> Duplikat
                        </button>
                    </div>
                    <span class="text-muted small">
                        <span id="selectedCount">0</span> baris dipilih
                    </span>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-inline-block">
                        {{ $rows->links() }}
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Empty State -->
        <div class="empty-state">
            <div class="text-center">
                <i class="fas fa-table empty-state-icon text-muted mb-3"></i>
                <h4 class="text-muted">Belum ada data</h4>
                <p class="text-muted mb-4">Mulai dengan menambahkan data melalui import Excel, OCR, atau tambah manual</p>
                @if($canEdit)
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-primary" id="addRowBtn">
                        <i class="fas fa-plus me-1"></i> Tambah Baris Manual
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-file-import me-1"></i> Import dari Excel
                    </button>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ocrModal">
                        <i class="fas fa-image me-1"></i> Tambah dari Foto
                    </button>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Anda hanya memiliki akses view-only untuk dataset ini.
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modals Section -->
<!-- Add Row Modal -->
<div class="modal fade" id="addRowModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i> Tambah Baris Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addRowForm">
                    <div class="row">
                        @foreach($dataset->columns as $column)
                        <div class="col-md-4 mb-3">
                            <label for="column_{{ $loop->index }}" class="form-label">
                                {{ $column }}
                                @if(in_array(strtolower($column), ['email', 'e-mail', 'mail']))
                                    <span class="text-muted small">(email)</span>
                                @elseif(in_array(strtolower($column), ['phone', 'telp', 'telepon', 'hp', 'no hp']))
                                    <span class="text-muted small">(telepon)</span>
                                @elseif(in_array(strtolower($column), ['date', 'tanggal', 'tgl', 'waktu']))
                                    <span class="text-muted small">(tanggal)</span>
                                @endif
                            </label>
                            <input type="text" class="form-control" 
                                   id="column_{{ $loop->index }}" 
                                   name="data[{{ $column }}]" 
                                   placeholder="Masukkan {{ $column }}">
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="saveNewRow()">
                    <i class="fas fa-save me-1"></i> Simpan Baris
                </button>
                <button type="button" class="btn btn-success" onclick="saveAndAddMore()">
                    <i class="fas fa-plus-circle me-1"></i> Simpan & Tambah Lagi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add sharing info section -->
@if($sharedUsers && count($sharedUsers) > 1)
<div class="alert alert-info mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-users"></i> 
            <strong>Shared with {{ count($sharedUsers) - 1 }} user(s):</strong>
            @foreach($sharedUsers as $shared)
                @if($shared['user']->id !== $dataset->user_id)
                    <span class="badge bg-light text-dark ms-1">
                        {{ $shared['user']->name }} ({{ $shared['permission'] }})
                    </span>
                @endif
            @endforeach
        </div>
        @if($dataset->user_id === auth()->id())
        <a href="{{ route('datasets.share.show', $dataset->id) }}" class="btn btn-sm btn-outline-info">
            <i class="fas fa-cog"></i> Manage Sharing
        </a>
        @endif
    </div>
</div>
@endif

<!-- Import Excel Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <input type="hidden" name="dataset_id" value="{{ $dataset->id }}">
                <input type="hidden" name="dataset_name" value="{{ $dataset->name }} - Import {{ date('Y-m-d') }}">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-import me-2"></i> Import dari Excel/CSV
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Pilih File Excel/CSV</label>
                        <input type="file" class="form-control" 
                               id="importFile" name="file" 
                               accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            Format: .xlsx, .xls, .csv (maks. 10MB)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Opsi Header</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="has_header" 
                                   id="hasHeaderYes" value="1" checked>
                            <label class="form-check-label" for="hasHeaderYes">
                                <i class="fas fa-heading text-success me-1"></i> Baris pertama sebagai NAMA KOLOM
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_header" 
                                   id="hasHeaderNo" value="0">
                            <label class="form-check-label" for="hasHeaderNo">
                                <i class="fas fa-list text-warning me-1"></i> Baris pertama sebagai DATA (kolom otomatis)
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Data akan ditambahkan ke dataset ini. Kolom baru akan dibuat otomatis.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- OCR Modal -->
<div class="modal fade" id="ocrModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('ocr.store') }}" method="POST" enctype="multipart/form-data" id="ocrForm">
                @csrf
                <input type="hidden" name="dataset_id" value="{{ $dataset->id }}">
                <input type="hidden" name="dataset_name" value="{{ $dataset->name }} - OCR {{ date('Y-m-d') }}">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-image me-2"></i> Tambah Data dari Foto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ocrImage" class="form-label">Pilih Foto Tabel</label>
                        <input type="file" class="form-control" 
                               id="ocrImage" name="image" 
                               accept="image/*" 
                               onchange="previewOcrImage(this)" required>
                        <div class="form-text">
                            Upload foto yang berisi tabel data. Pastikan teks terbaca jelas.
                        </div>
                    </div>
                    
                    <div class="mb-3" id="ocrImagePreviewContainer" style="display: none;">
                        <label class="form-label">Pratinjau Gambar</label>
                        <div class="border rounded p-2 text-center bg-light">
                            <img id="ocrImagePreview" src="#" alt="Preview" 
                                 style="max-width: 100%; max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Opsi Header OCR</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="has_header" 
                                   id="ocrHasHeaderYes" value="1" checked>
                            <label class="form-check-label" for="ocrHasHeaderYes">
                                <i class="fas fa-heading text-success me-1"></i> Baris pertama sebagai NAMA KOLOM
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_header" 
                                   id="ocrHasHeaderNo" value="0">
                            <label class="form-check-label" for="ocrHasHeaderNo">
                                <i class="fas fa-list text-warning me-1"></i> Baris pertama sebagai DATA (kolom otomatis)
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Tips:</strong> 
                        <ul class="mb-0">
                            <li>Gunakan foto dengan pencahayaan baik</li>
                            <li>Pastikan tabel lurus dan tidak miring</li>
                            <li>Hasil OCR mungkin perlu diperiksa ulang</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cogs me-1"></i> Proses OCR
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Columns Modal -->
<div class="modal fade" id="manageColumnsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cog me-2"></i> Kelola Kolom
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6>Daftar Kolom</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Kolom</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="columnsList">
                                @foreach($dataset->columns as $index => $column)
                                <tr data-column="{{ $column }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="column-name">{{ $column }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning me-1" 
                                                onclick="showRenameColumn('{{ $column }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="showDeleteColumn('{{ $column }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6>Ubah Urutan Kolom (Drag & Drop)</h6>
                    <div id="sortableColumns" class="list-group">
                        @foreach($dataset->columns as $column)
                        <div class="list-group-item draggable-column" data-column="{{ $column }}">
                            <i class="fas fa-grip-vertical me-2"></i>
                            <span class="badge bg-primary me-2">{{ $loop->iteration }}</span>
                            {{ $column }}
                        </div>
                        @endforeach
                    </div>
                    <button class="btn btn-sm btn-primary mt-2" onclick="saveColumnOrder()">
                        <i class="fas fa-save me-1"></i> Simpan Urutan
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Row Modal -->
<div class="modal fade" id="editRowModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i> Edit Baris Lengkap
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editRowModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable if there's data
    @if($dataset->row_count > 0)
    initializeDataTable();
    initializeHorizontalScroll();
    @endif
    
    // Initialize sortable columns
    $("#sortableColumns").sortable({
        placeholder: "ui-state-highlight",
        update: function(event, ui) {
            // Update badge numbers
            $('#sortableColumns .draggable-column').each(function(index) {
                $(this).find('.badge').text(index + 1);
            });
        }
    });
    $("#sortableColumns").disableSelection();
    
    // Add row button click
    $('#addRowBtn').click(function() {
        $('#addRowModal').modal('show');
    });
    
    // Handle import form submission
    $('#importForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        Swal.fire({
            title: 'Sedang Mengimport...',
            text: 'Harap tunggu, data sedang diproses',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                $('#importModal').modal('hide');
                showSuccess('Data berhasil diimport!');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                Swal.close();
                showError('Gagal mengimport data: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });
    
    // Handle OCR form submission
    $('#ocrForm').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        Swal.fire({
            title: 'Memproses OCR...',
            text: 'Sedang membaca data dari gambar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                $('#ocrModal').modal('hide');
                showSuccess('OCR berhasil diproses!');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                Swal.close();
                showError('Gagal memproses OCR: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });
    
    // Update selected count
    updateSelectedCount();
});

function initializeDataTable() {
    $('#dataTable').DataTable({
        responsive: false,
        scrollX: true,
        scrollY: '500px',
        scrollCollapse: true,
        paging: false,
        searching: true,
        ordering: true,
        info: false,
        fixedColumns: {
            left: 2,
            right: 1
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function() {
            attachEventListeners();
        },
        drawCallback: function() {
            attachEventListeners();
        }
    });
}

function initializeHorizontalScroll() {
    const scrollContainer = $('.table-scroll-container');
    const scrollIndicator = $('.scroll-indicator');
    const tableWrapper = $('.table-wrapper');
    
    function updateScrollIndicator() {
        const scrollLeft = scrollContainer.scrollLeft();
        const scrollWidth = scrollContainer[0].scrollWidth;
        const clientWidth = scrollContainer[0].clientWidth;
        
        if (scrollLeft + clientWidth >= scrollWidth - 10) {
            scrollIndicator.fadeOut(300);
        } else {
            scrollIndicator.fadeIn(300);
        }
    }
    
    // Auto-scroll on hover near edges
    let scrollInterval;
    
    scrollContainer.on('mousemove', function(e) {
        const container = $(this);
        const containerWidth = container.width();
        const mouseX = e.pageX - container.offset().left;
        const scrollSpeed = 8;
        
        clearInterval(scrollInterval);
        
        if (mouseX < 50) {
            // Near left edge, scroll left
            scrollInterval = setInterval(() => {
                container.scrollLeft(container.scrollLeft() - scrollSpeed);
            }, 16);
        } else if (mouseX > containerWidth - 50) {
            // Near right edge, scroll right
            scrollInterval = setInterval(() => {
                container.scrollLeft(container.scrollLeft() + scrollSpeed);
            }, 16);
        }
    });
    
    scrollContainer.on('mouseleave', function() {
        clearInterval(scrollInterval);
    });
    
    scrollContainer.on('scroll', updateScrollIndicator);
    $(window).on('resize', updateScrollIndicator);
    updateScrollIndicator();
}

function attachEventListeners() {
    // Delete row
    $('.delete-row').off('click').on('click', function() {
        const rowId = $(this).data('row-id');
        deleteRow(rowId);
    });
    
    // Edit row
    $('.edit-row').off('click').on('click', function() {
        const rowId = $(this).data('row-id');
        editFullRow(rowId);
    });
    
    // Checkbox events
    $('#selectAllRows').off('change').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked);
        $('.data-row').toggleClass('selected', isChecked);
        updateSelectedCount();
    });
    
    $('.row-checkbox').off('change').on('change', function() {
        const rowId = $(this).data('row-id');
        const $row = $('#row-' + rowId);
        $row.toggleClass('selected', $(this).is(':checked'));
        updateSelectedCount();
        
        // Update select all checkbox
        const totalCheckboxes = $('.row-checkbox').length;
        const checkedCheckboxes = $('.row-checkbox:checked').length;
        $('#selectAllRows').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Inline editing on double click
    $('.editable-cell').off('dblclick').on('dblclick', function() {
        if ($(this).hasClass('editing')) return;
        
        const $cell = $(this);
        const originalValue = $cell.data('original-value') || '';
        const column = $cell.data('column');
        const rowId = $cell.data('row-id');
        
        startInlineEditing($cell, originalValue, column, rowId);
    });
}

function updateSelectedCount() {
    const selectedCount = $('.row-checkbox:checked').length;
    $('#selectedCount').text(selectedCount);
}

function startInlineEditing($cell, originalValue, column, rowId) {
    $cell.addClass('editing');
    $cell.html(`
        <div class="edit-input-group input-group input-group-sm">
            <input type="text" class="form-control edit-input" 
                   value="${originalValue}" 
                   data-original="${originalValue}"
                   placeholder="Masukkan ${column}">
            <button class="btn btn-outline-success btn-save" type="button">
                <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-outline-danger btn-cancel" type="button">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `);
    
    const $input = $cell.find('.edit-input');
    $input.focus();
    $input.select();
    
    // Handle Enter key
    $input.on('keypress', function(e) {
        if (e.which === 13) {
            saveCellValue($cell, rowId, column);
        }
    });
    
    // Handle Escape key
    $input.on('keydown', function(e) {
        if (e.key === 'Escape') {
            cancelEdit($cell, originalValue);
        }
    });
    
    // Save button click
    $cell.find('.btn-save').on('click', function() {
        saveCellValue($cell, rowId, column);
    });
    
    // Cancel button click
    $cell.find('.btn-cancel').on('click', function() {
        cancelEdit($cell, originalValue);
    });
}

function saveCellValue($cell, rowId, column) {
    const newValue = $cell.find('.edit-input').val();
    const originalValue = $cell.find('.edit-input').data('original');
    
    if (newValue === originalValue) {
        $cell.text(newValue).removeClass('editing');
        $cell.data('original-value', newValue);
        return;
    }
    
    $('#tableLoading').show();
    
    $.ajax({
        url: '/datasets/{{ $dataset->id }}/rows/' + rowId,
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            column: column,
            value: newValue
        },
        success: function(response) {
            $('#tableLoading').hide();
            $cell.html(`<div class="cell-content" title="${newValue}">${newValue}</div>`)
                .data('original-value', newValue)
                .removeClass('editing');
            showSuccess('Data berhasil diperbarui!');
        },
        error: function(xhr) {
            $('#tableLoading').hide();
            showError('Gagal memperbarui data');
            cancelEdit($cell, originalValue);
        }
    });
}

function cancelEdit($cell, originalValue) {
    $cell.html(`<div class="cell-content" title="${originalValue}">${originalValue}</div>`)
        .data('original-value', originalValue)
        .removeClass('editing');
}

function saveNewRow() {
    const formData = new FormData(document.getElementById('addRowForm'));
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        const match = key.match(/data\[(.+)\]/);
        if (match) {
            data[match[1]] = value;
        }
    }
    
    $('#tableLoading').show();
    
    $.ajax({
        url: '{{ route("datasets.addRow", $dataset->id) }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            data: data
        },
        success: function(response) {
            $('#tableLoading').hide();
            $('#addRowModal').modal('hide');
            showSuccess('Baris berhasil ditambahkan!');
            setTimeout(() => location.reload(), 1000);
        },
        error: function(xhr) {
            $('#tableLoading').hide();
            showError('Gagal menambahkan baris');
        }
    });
}

function saveAndAddMore() {
    const formData = new FormData(document.getElementById('addRowForm'));
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        const match = key.match(/data\[(.+)\]/);
        if (match) {
            data[match[1]] = value;
        }
    }
    
    $.ajax({
        url: '{{ route("datasets.addRow", $dataset->id) }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            data: data
        },
        success: function(response) {
            // Clear form
            $('#addRowForm')[0].reset();
            showSuccess('Baris berhasil ditambahkan! Silakan tambah lagi.');
        },
        error: function(xhr) {
            showError('Gagal menambahkan baris');
        }
    });
}

function showAddColumnModal() {
    Swal.fire({
        title: 'Tambah Kolom Baru',
        input: 'text',
        inputLabel: 'Nama Kolom',
        inputPlaceholder: 'Masukkan nama kolom baru',
        showCancelButton: true,
        confirmButtonText: 'Tambah',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) {
                return 'Nama kolom harus diisi!';
            }
            if (value.length > 100) {
                return 'Nama kolom maksimal 100 karakter!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $('#tableLoading').show();
            
            $.ajax({
                url: '{{ route("datasets.addColumn", $dataset->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    column_name: result.value
                },
                success: function(response) {
                    $('#tableLoading').hide();
                    showSuccess('Kolom berhasil ditambahkan!');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    $('#tableLoading').hide();
                    showError('Gagal menambahkan kolom');
                }
            });
        }
    });
}

function renameColumn(columnName) {
    Swal.fire({
        title: 'Ubah Nama Kolom',
        input: 'text',
        inputLabel: 'Nama Baru',
        inputValue: columnName,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) {
                return 'Nama kolom harus diisi!';
            }
            if (value.length > 100) {
                return 'Nama kolom maksimal 100 karakter!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value !== columnName) {
            $('#tableLoading').show();
            
            $.ajax({
                url: '{{ route("datasets.renameColumn", $dataset->id) }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    old_name: columnName,
                    new_name: result.value
                },
                success: function(response) {
                    $('#tableLoading').hide();
                    showSuccess('Nama kolom berhasil diubah!');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    $('#tableLoading').hide();
                    showError('Gagal mengubah nama kolom');
                }
            });
        }
    });
}

function showRenameColumn(columnName) {
    renameColumn(columnName);
}

function deleteColumn(columnName) {
    Swal.fire({
        title: 'Hapus Kolom?',
        html: `Apakah Anda yakin ingin menghapus kolom <strong>${columnName}</strong>?<br>
               Semua data dalam kolom ini akan dihapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#tableLoading').show();
            
            $.ajax({
                url: '{{ route("datasets.deleteColumn", $dataset->id) }}',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    column_name: columnName
                },
                success: function(response) {
                    $('#tableLoading').hide();
                    showSuccess('Kolom berhasil dihapus!');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    $('#tableLoading').hide();
                    showError('Gagal menghapus kolom');
                }
            });
        }
    });
}

function showDeleteColumn(columnName) {
    deleteColumn(columnName);
}

function saveColumnOrder() {
    const columns = [];
    $('#sortableColumns .draggable-column').each(function() {
        columns.push($(this).data('column'));
    });
    
    $('#tableLoading').show();
    
    $.ajax({
        url: '{{ route("datasets.reorderColumns", $dataset->id) }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            columns: columns
        },
        success: function(response) {
            $('#tableLoading').hide();
            showSuccess('Urutan kolom berhasil disimpan!');
            setTimeout(() => location.reload(), 1000);
        },
        error: function(xhr) {
            $('#tableLoading').hide();
            showError('Gagal menyimpan urutan kolom');
        }
    });
}

function previewOcrImage(input) {
    const previewContainer = document.getElementById('ocrImagePreviewContainer');
    const preview = document.getElementById('ocrImagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '#';
        previewContainer.style.display = 'none';
    }
}

function deleteRow(rowId) {
    Swal.fire({
        title: 'Hapus Baris?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#tableLoading').show();
            
            $.ajax({
                url: '/datasets/{{ $dataset->id }}/rows/' + rowId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#tableLoading').hide();
                    $('#row-' + rowId).remove();
                    showSuccess('Baris berhasil dihapus!');
                    
                    // Update row count badge
                    const currentCount = parseInt($('.row-count-badge').text());
                    $('.row-count-badge').text(currentCount - 1);
                    
                    updateSelectedCount();
                },
                error: function(xhr) {
                    $('#tableLoading').hide();
                    showError('Gagal menghapus baris');
                }
            });
        }
    });
}

function editFullRow(rowId) {
    $.ajax({
        url: '/datasets/{{ $dataset->id }}/rows/' + rowId + '/edit-form',
        type: 'GET',
        beforeSend: function() {
            $('#editRowModalBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data...</p>
                </div>
            `);
            $('#editRowModal').modal('show');
        },
        success: function(response) {
            $('#editRowModalBody').html(response.html);
        },
        error: function(xhr) {
            $('#editRowModal').modal('hide');
            showError('Gagal memuat data baris');
        }
    });
}

function selectAllRows() {
    const isChecked = !$('#selectAllRows').is(':checked');
    $('.row-checkbox').prop('checked', isChecked);
    $('.data-row').toggleClass('selected', isChecked);
    $('#selectAllRows').prop('checked', isChecked);
    updateSelectedCount();
}

function deleteSelectedRows() {
    const selectedRows = [];
    $('.row-checkbox:checked').each(function() {
        selectedRows.push($(this).data('row-id'));
    });
    
    if (selectedRows.length === 0) {
        showError('Pilih baris yang akan dihapus terlebih dahulu');
        return;
    }
    
    Swal.fire({
        title: 'Hapus Baris Terpilih?',
        html: `Anda akan menghapus <strong>${selectedRows.length}</strong> baris.<br>
               Data yang dihapus tidak dapat dikembalikan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#tableLoading').show();
            
            $.ajax({
                url: '/datasets/{{ $dataset->id }}/rows/delete-selected',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    row_ids: selectedRows
                },
                success: function(response) {
                    $('#tableLoading').hide();
                    selectedRows.forEach(function(rowId) {
                        $('#row-' + rowId).remove();
                    });
                    showSuccess(`${response.count} baris berhasil dihapus!`);
                    
                    // Update row count
                    const currentCount = parseInt($('.row-count-badge').text());
                    $('.row-count-badge').text(currentCount - response.count);
                    
                    updateSelectedCount();
                },
                error: function(xhr) {
                    $('#tableLoading').hide();
                    showError('Gagal menghapus baris terpilih');
                }
            });
        }
    });
}

function duplicateSelectedRows() {
    const selectedRows = [];
    $('.row-checkbox:checked').each(function() {
        selectedRows.push($(this).data('row-id'));
    });
    
    if (selectedRows.length === 0) {
        showError('Pilih baris yang akan diduplikasi terlebih dahulu');
        return;
    }
    
    Swal.fire({
        title: 'Duplikat Baris?',
        html: `Anda akan menduplikasi <strong>${selectedRows.length}</strong> baris.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, duplikasi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#tableLoading').show();
            
            $.ajax({
                url: '/datasets/{{ $dataset->id }}/rows/duplicate',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    row_ids: selectedRows
                },
                success: function(response) {
                    $('#tableLoading').hide();
                    showSuccess(`${response.count} baris berhasil diduplikasi!`);
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    $('#tableLoading').hide();
                    showError('Gagal menduplikasi baris');
                }
            });
        }
    });
}

function exportData() {
    window.location.href = '{{ route("datasets.export", $dataset->id) }}';
}

// Utility functions
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Sukses',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'OK'
    });
}
</script>
@endsection