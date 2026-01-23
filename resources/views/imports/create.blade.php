@extends('layouts.app')

@section('title', 'Import Excel/CSV')

@section('styles')
<style>
    .preview-table-container {
        max-height: 400px;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .preview-table {
        margin: 0;
        min-width: 100%;
    }
    .preview-table th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 10;
    }
    .file-info-card {
        transition: all 0.3s;
    }
    .column-badge {
        font-size: 0.8em;
        margin-right: 3px;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-import"></i> Import dari Excel/CSV
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-upload"></i> Upload File
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('imports.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="file" class="form-label">
                                    <strong>Pilih File Data</strong>
                                </label>
                                <div class="file-upload-area border rounded p-4 text-center bg-light">
                                    <input type="file" class="form-control d-none" 
                                           id="file" name="file" 
                                           accept=".xlsx,.xls,.csv" required
                                           onchange="previewFile(this)">
                                    
                                    <div id="uploadArea" class="py-5" onclick="document.getElementById('file').click()">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Klik atau drag file Excel/CSV ke sini</h5>
                                        <p class="text-muted">Format: .xlsx, .xls, .csv (maks. 10MB)</p>
                                        <button type="button" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-search"></i> Browse Files
                                        </button>
                                    </div>
                                    
                                    <div id="fileInfo" style="display: none;">
                                        <div class="alert alert-success mb-0">
                                            <h6><i class="fas fa-check-circle"></i> File Dipilih</h6>
                                            <p id="fileName" class="mb-1"></p>
                                            <p id="fileSize" class="mb-1 small"></p>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeFile()">
                                                <i class="fas fa-times"></i> Ganti File
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>Pilihan Dataset</strong>
                                </label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="dataset_option" 
                                           id="newDataset" value="new" checked 
                                           onchange="toggleDatasetOption()">
                                    <label class="form-check-label" for="newDataset">
                                        <i class="fas fa-plus-circle text-primary"></i> Buat Dataset Baru
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="dataset_option" 
                                           id="existingDataset" value="existing"
                                           onchange="toggleDatasetOption()">
                                    <label class="form-check-label" for="existingDataset">
                                        <i class="fas fa-database text-success"></i> Tambah ke Dataset yang Ada
                                    </label>
                                </div>
                            </div>
                            
                            <div id="newDatasetSection">
                                <div class="mb-3">
                                    <label for="dataset_name" class="form-label">Nama Dataset Baru</label>
                                    <input type="text" class="form-control" 
                                           id="dataset_name" name="dataset_name" 
                                           value="{{ old('dataset_name', 'Data Import ' . date('d-m-Y')) }}"
                                           placeholder="Contoh: Data Pelanggan Januari 2024">
                                </div>
                            </div>
                            
                            <div id="existingDatasetSection" style="display: none;">
                                <div class="mb-3">
                                    <label for="dataset_id" class="form-label">Pilih Dataset</label>
                                    <select class="form-select" id="dataset_id" name="dataset_id">
                                        <option value="">-- Pilih Dataset --</option>
                                        @foreach($datasets as $dataset)
                                            <option value="{{ $dataset->id }}">
                                                {{ $dataset->name }} ({{ $dataset->row_count }} baris)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>Opsi Import</strong>
                                </label>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_header" 
                                               id="hasHeaderYes" value="1" checked
                                               onchange="updatePreview()">
                                        <label class="form-check-label" for="hasHeaderYes">
                                            <i class="fas fa-heading text-success"></i> Baris pertama sebagai NAMA KOLOM
                                        </label>
                                        <div class="form-text">
                                            Gunakan jika file memiliki header di baris pertama
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_header" 
                                               id="hasHeaderNo" value="0"
                                               onchange="updatePreview()">
                                        <label class="form-check-label" for="hasHeaderNo">
                                            <i class="fas fa-list text-warning"></i> Baris pertama sebagai DATA (buat nama kolom otomatis)
                                        </label>
                                        <div class="form-text">
                                            Gunakan jika file tidak memiliki header
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- File Preview -->
                            <div id="previewSection" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <strong>Pratinjau Data</strong>
                                        <span id="previewInfo" class="badge bg-info ms-2"></span>
                                    </label>
                                    
                                    <div id="columnsPreview" class="mb-3">
                                        <div class="d-flex flex-wrap gap-2" id="columnsList"></div>
                                    </div>
                                    
                                    <div class="preview-table-container">
                                        <table class="table table-sm preview-table table-bordered">
                                            <thead id="previewTableHead"></thead>
                                            <tbody id="previewTableBody"></tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> Menampilkan 5 baris pertama dari total <span id="totalRows">0</span> baris
                                    </div>
                                </div>
                            </div>
                            
                            <div id="noPreviewSection" class="text-center text-muted py-4">
                                <i class="fas fa-table fa-2x mb-3"></i>
                                <p>Pilih file untuk melihat pratinjau data</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle"></i> Informasi Import</h6>
                        <ul class="mb-0">
                            <li>Sistem akan membuat tabel dengan struktur yang sama seperti file</li>
                            <li>Kolom akan dibuat otomatis sesuai file</li>
                            <li>Data akan langsung tersedia untuk di-edit setelah import</li>
                            <li>Import besar mungkin memerlukan waktu beberapa menit</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-upload"></i> Import & Buat Tabel
                        </button>
                        <a href="{{ route('imports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentFile = null;

function toggleDatasetOption() {
    const isNew = document.getElementById('newDataset').checked;
    document.getElementById('newDatasetSection').style.display = isNew ? 'block' : 'none';
    document.getElementById('existingDatasetSection').style.display = isNew ? 'none' : 'block';
    
    if (isNew) {
        document.getElementById('dataset_name').required = true;
        document.getElementById('dataset_id').required = false;
    } else {
        document.getElementById('dataset_name').required = false;
        document.getElementById('dataset_id').required = true;
    }
}

function previewFile(input) {
    if (!input.files || !input.files[0]) {
        return;
    }
    
    currentFile = input.files[0];
    const uploadArea = document.getElementById('uploadArea');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    // Show file info
    fileName.textContent = currentFile.name;
    fileSize.textContent = formatFileSize(currentFile.size);
    uploadArea.style.display = 'none';
    fileInfo.style.display = 'block';
    
    // Show preview section
    document.getElementById('noPreviewSection').style.display = 'none';
    document.getElementById('previewSection').style.display = 'block';
    
    // Update preview
    updatePreview();
}

function removeFile() {
    document.getElementById('file').value = '';
    currentFile = null;
    document.getElementById('uploadArea').style.display = 'block';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('noPreviewSection').style.display = 'block';
    document.getElementById('previewSection').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function updatePreview() {
    if (!currentFile) return;
    
    const hasHeader = document.querySelector('input[name="has_header"]:checked').value;
    
    // Show loading
    document.getElementById('previewInfo').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
    document.getElementById('columnsList').innerHTML = '';
    document.getElementById('previewTableHead').innerHTML = '';
    document.getElementById('previewTableBody').innerHTML = '';
    
    const formData = new FormData();
    formData.append('file', currentFile);
    formData.append('has_header', hasHeader);
    
    fetch('{{ route("imports.preview") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPreview(data);
        } else {
            showError('Gagal memuat pratinjau: ' + data.message);
        }
    })
    .catch(error => {
        showError('Error: ' + error.message);
    });
}

function displayPreview(data) {
    // Update preview info
    const headerText = data.has_header ? 'dengan Header' : 'tanpa Header';
    document.getElementById('previewInfo').innerHTML = 
        `${data.columns.length} kolom, ${data.total_rows} baris (${headerText})`;
    document.getElementById('totalRows').textContent = data.total_rows;
    
    // Display columns
    const columnsList = document.getElementById('columnsList');
    data.columns.forEach((col, index) => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary column-badge';
        badge.title = col;
        badge.textContent = `${index + 1}. ${col.length > 15 ? col.substring(0, 15) + '...' : col}`;
        columnsList.appendChild(badge);
    });
    
    // Display table header
    const tableHead = document.getElementById('previewTableHead');
    const headerRow = document.createElement('tr');
    
    // Add index column
    const thIndex = document.createElement('th');
    thIndex.textContent = '#';
    thIndex.style.width = '50px';
    headerRow.appendChild(thIndex);
    
    // Add data columns
    data.columns.forEach(col => {
        const th = document.createElement('th');
        th.textContent = col;
        th.style.minWidth = '150px';
        headerRow.appendChild(th);
    });
    
    tableHead.appendChild(headerRow);
    
    // Display table body
    const tableBody = document.getElementById('previewTableBody');
    data.preview.forEach((row, rowIndex) => {
        const tr = document.createElement('tr');
        
        // Add row number
        const tdIndex = document.createElement('td');
        tdIndex.textContent = rowIndex + 1;
        tdIndex.className = 'text-center fw-bold';
        tr.appendChild(tdIndex);
        
        // Add data cells
        data.columns.forEach((col, colIndex) => {
            const td = document.createElement('td');
            const value = row[colIndex] || row[col] || '';
            td.textContent = value;
            td.style.maxWidth = '200px';
            td.style.overflow = 'hidden';
            td.style.textOverflow = 'ellipsis';
            td.style.whiteSpace = 'nowrap';
            td.title = value;
            tr.appendChild(td);
        });
        
        tableBody.appendChild(tr);
    });
}

// Drag and drop functionality
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('file');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.backgroundColor = '#e9ecef';
});

uploadArea.addEventListener('dragleave', (e) => {
    e.preventDefault();
    uploadArea.style.backgroundColor = '';
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.style.backgroundColor = '';
    
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        previewFile(fileInput);
    }
});

// Handle form submission
document.getElementById('importForm').addEventListener('submit', function(e) {
    if (!currentFile) {
        e.preventDefault();
        showError('Pilih file terlebih dahulu');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengimport...';
    submitBtn.disabled = true;
});

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