@extends('layouts.app')

@section('title', 'OCR dari Foto')

@section('styles')
<style>
    .file-upload-area {
        cursor: pointer;
        transition: all 0.3s;
    }
    .file-upload-area:hover {
        background-color: #f8f9fa;
        border-color: #0d6efd !important;
    }
    .file-upload-area.dragover {
        background-color: #e7f1ff;
        border-color: #0d6efd !important;
        transform: scale(1.02);
    }
    #imagePreview {
        max-height: 300px;
        object-fit: contain;
    }
    .ocr-processing {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    .ocr-processing.active {
        display: flex;
    }
    .ocr-preview-table {
        font-size: 0.9rem;
    }
    .ocr-preview-table th {
        white-space: nowrap;
        background: #f8f9fa;
    }
    .ocr-preview-table td {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="ocr-processing" id="ocrProcessing">
    <div class="text-center">
        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h4 class="mt-3">Memproses OCR...</h4>
        <p class="text-muted">Sedang membaca tabel dari gambar</p>
        <div class="progress mt-3" style="width: 300px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-image"></i> OCR dari Foto Tabel
    </h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-camera"></i> Upload Foto Tabel
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('ocr.store') }}" method="POST" enctype="multipart/form-data" id="ocrForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="image" class="form-label">
                                    <strong>Pilih Foto Tabel</strong>
                                </label>
                                <div class="file-upload-area border rounded p-4 text-center" 
                                     id="uploadArea"
                                     ondragover="handleDragOver(event)"
                                     ondragleave="handleDragLeave(event)"
                                     ondrop="handleDrop(event)">
                                    <input type="file" class="form-control d-none" 
                                           id="image" name="image" 
                                           accept="image/*" required
                                           onchange="handleImageSelect(event)">
                                    
                                    <div id="uploadPrompt" class="py-5">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Klik atau drag file gambar ke sini</h5>
                                        <p class="text-muted mb-3">Format: JPG, PNG, GIF (maks. 5MB)</p>
                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('image').click()">
                                            <i class="fas fa-search"></i> Pilih File
                                        </button>
                                    </div>
                                    
                                    <div id="imagePreviewContainer" style="display: none;">
                                        <div class="position-relative">
                                            <img id="imagePreview" src="#" alt="Preview" 
                                                 class="img-fluid rounded border mb-3">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                                    onclick="removeImage()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('image').click()">
                                                <i class="fas fa-sync"></i> Ganti Gambar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Upload foto tabel yang jelas dan terang
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>Opsi OCR</strong>
                                </label>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_header" 
                                               id="hasHeaderYes" value="1" checked
                                               onchange="updateOcrPreview()">
                                        <label class="form-check-label" for="hasHeaderYes">
                                            <i class="fas fa-heading text-success"></i> Baris pertama sebagai NAMA KOLOM
                                        </label>
                                        <div class="form-text">
                                            Gunakan jika tabel memiliki header di baris pertama
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="has_header" 
                                               id="hasHeaderNo" value="0"
                                               onchange="updateOcrPreview()">
                                        <label class="form-check-label" for="hasHeaderNo">
                                            <i class="fas fa-list text-warning"></i> Baris pertama sebagai DATA (buat nama kolom otomatis)
                                        </label>
                                        <div class="form-text">
                                            Gunakan jika tabel tidak memiliki header
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">
                                    <strong>Pilihan Dataset</strong>
                                </label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="dataset_option" 
                                           id="newDataset" value="new" checked 
                                           onchange="toggleDatasetOption()">
                                    <label class="form-check-label" for="newDataset">
                                        <i class="fas fa-plus-circle text-primary"></i> Buat Dataset Baru dari OCR
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
                                           value="{{ old('dataset_name', 'Data OCR ' . date('d-m-Y')) }}"
                                           placeholder="Contoh: Data Tabel dari Invoice">
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
                            
                            <!-- OCR Preview Section -->
                            <div id="ocrPreviewSection" style="display: none;">
                                <div class="mb-4">
                                    <label class="form-label">
                                        <strong>Pratinjau Hasil OCR</strong>
                                        <span id="ocrPreviewInfo" class="badge bg-info ms-2"></span>
                                    </label>
                                    
                                    <div id="columnsPreview" class="mb-3">
                                        <div class="d-flex flex-wrap gap-2" id="ocrColumnsList"></div>
                                    </div>
                                    
                                    <div class="table-responsive" style="max-height: 300px;">
                                        <table class="table table-sm ocr-preview-table table-bordered">
                                            <thead id="ocrPreviewTableHead"></thead>
                                            <tbody id="ocrPreviewTableBody"></tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-info-circle"></i> Menampilkan 5 baris pertama dari total <span id="ocrTotalRows">0</span> baris yang terdeteksi
                                    </div>
                                </div>
                            </div>
                            
                            <div id="noOcrPreviewSection" class="text-center text-muted py-4">
                                <i class="fas fa-table fa-2x mb-3"></i>
                                <p>Pilih gambar tabel untuk melihat pratinjau hasil OCR</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <h6><i class="fas fa-exclamation-triangle"></i> Tips untuk Hasil Terbaik</h6>
                        <ul class="mb-0">
                            <li><strong>Kualitas Gambar:</strong> Gunakan foto dengan resolusi tinggi</li>
                            <li><strong>Pencahayaan:</strong> Pastikan area tabel terang dan jelas</li>
                            <li><strong>Sudut:</strong> Ambil foto lurus dari atas tabel</li>
                            <li><strong>Fokus:</strong> Pastikan teks tajam dan tidak blur</li>
                            <li><strong>Kontras:</strong> Tabel dengan background kontras lebih mudah dibaca</li>
                            <li><strong>Format:</strong> Tabel dengan garis pembatas memberikan hasil terbaik</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-cogs"></i> Proses OCR & Buat Tabel
                        </button>
                        <a href="{{ route('ocr.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Contoh Tabel untuk Panduan -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb"></i> Contoh Tabel yang Baik untuk OCR
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-success"><i class="fas fa-check-circle"></i> BAIK</h6>
                                <img src="https://via.placeholder.com/400x300/198754/ffffff?text=Tabel+Jelas+Header+Terbaca" 
                                     alt="Contoh Tabel Baik" class="img-fluid rounded border">
                                <ul class="mt-2 small">
                                    <li>Pencahayaan merata</li>
                                    <li>Teks tajam dan jelas</li>
                                    <li>Background kontras</li>
                                    <li>Tabel lurus</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h6 class="text-danger"><i class="fas fa-times-circle"></i> KURANG BAIK</h6>
                                <img src="https://via.placeholder.com/400x300/dc3545/ffffff?text=Tabel+Blur+Silau" 
                                     alt="Contoh Tabel Kurang Baik" class="img-fluid rounded border">
                                <ul class="mt-2 small">
                                    <li>Pencahayaan tidak merata</li>
                                    <li>Teks blur/kabur</li>
                                    <li>Background tidak kontras</li>
                                    <li>Tabel miring</li>
                                </ul>
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
let currentImage = null;

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

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.add('dragover');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.remove('dragover');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.remove('dragover');
    
    if (e.dataTransfer.files.length) {
        const fileInput = document.getElementById('image');
        fileInput.files = e.dataTransfer.files;
        handleImageSelect({ target: fileInput });
    }
}

function handleImageSelect(event) {
    if (!event.target.files || !event.target.files[0]) {
        return;
    }
    
    currentImage = event.target.files[0];
    const uploadPrompt = document.getElementById('uploadPrompt');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('imagePreview');
    const reader = new FileReader();
    
    reader.onload = function(e) {
        preview.src = e.target.result;
        uploadPrompt.style.display = 'none';
        previewContainer.style.display = 'block';
        
        // Enable submit button
        document.getElementById('submitBtn').disabled = false;
        
        // Show preview sections
        document.getElementById('noOcrPreviewSection').style.display = 'none';
        document.getElementById('ocrPreviewSection').style.display = 'block';
        
        // Update OCR preview
        updateOcrPreview();
    }
    
    reader.readAsDataURL(currentImage);
}

function removeImage() {
    currentImage = null;
    document.getElementById('image').value = '';
    document.getElementById('uploadPrompt').style.display = 'block';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('noOcrPreviewSection').style.display = 'block';
    document.getElementById('ocrPreviewSection').style.display = 'none';
}

function updateOcrPreview() {
    if (!currentImage) return;
    
    const hasHeader = document.querySelector('input[name="has_header"]:checked').value;
    
    // Show loading
    document.getElementById('ocrPreviewInfo').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    document.getElementById('ocrColumnsList').innerHTML = '';
    document.getElementById('ocrPreviewTableHead').innerHTML = '';
    document.getElementById('ocrPreviewTableBody').innerHTML = '';
    
    const formData = new FormData();
    formData.append('image', currentImage);
    formData.append('has_header', hasHeader);
    
    // Show processing overlay
    document.getElementById('ocrProcessing').classList.add('active');
    
    fetch('{{ route("ocr.preview") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('ocrProcessing').classList.remove('active');
        
        if (data.success) {
            displayOcrPreview(data);
        } else {
            showError('Gagal memproses OCR: ' + data.message);
            document.getElementById('ocrPreviewSection').style.display = 'none';
            document.getElementById('noOcrPreviewSection').style.display = 'block';
        }
    })
    .catch(error => {
        document.getElementById('ocrProcessing').classList.remove('active');
        showError('Error: ' + error.message);
    });
}

function displayOcrPreview(data) {
    // Update preview info
    const headerText = data.has_header ? 'dengan Header' : 'tanpa Header';
    document.getElementById('ocrPreviewInfo').innerHTML = 
        `${data.columns.length} kolom, ${data.total_rows} baris (${headerText})`;
    document.getElementById('ocrTotalRows').textContent = data.total_rows;
    
    // Display columns as badges
    const columnsList = document.getElementById('ocrColumnsList');
    data.columns.forEach((col, index) => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary column-badge';
        badge.title = col;
        badge.textContent = `${index + 1}. ${col.length > 15 ? col.substring(0, 15) + '...' : col}`;
        columnsList.appendChild(badge);
    });
    
    // Display table header
    const tableHead = document.getElementById('ocrPreviewTableHead');
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
        th.style.minWidth = '120px';
        headerRow.appendChild(th);
    });
    
    tableHead.appendChild(headerRow);
    
    // Display table body
    const tableBody = document.getElementById('ocrPreviewTableBody');
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
            const value = row[colIndex] || '';
            td.textContent = value;
            td.style.maxWidth = '150px';
            td.style.overflow = 'hidden';
            td.style.textOverflow = 'ellipsis';
            td.style.whiteSpace = 'nowrap';
            td.title = value;
            tr.appendChild(td);
        });
        
        tableBody.appendChild(tr);
    });
}

// Handle form submission
document.getElementById('ocrForm').addEventListener('submit', function(e) {
    if (!currentImage) {
        e.preventDefault();
        showError('Pilih gambar terlebih dahulu');
        return;
    }
    
    // Show processing overlay
    document.getElementById('ocrProcessing').classList.add('active');
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
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