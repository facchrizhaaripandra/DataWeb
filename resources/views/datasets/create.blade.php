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
        <!-- Creation Method Selection -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pilih Metode Pembuatan Dataset</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="creation_method" id="manual" value="manual" checked>
                            <label class="form-check-label" for="manual">
                                <i class="fas fa-edit text-primary"></i>
                                <strong>Manual</strong><br>
                                <small class="text-muted">Buat dataset kosong dan tambahkan kolom secara manual</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="creation_method" id="ocr" value="ocr">
                            <label class="form-check-label" for="ocr">
                                <i class="fas fa-image text-success"></i>
                                <strong>Dari Gambar OCR</strong><br>
                                <small class="text-muted">Upload gambar tabel dan ekstrak data otomatis</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Creation Form -->
        <div class="card" id="manualForm">
            <div class="card-header">
                <h5 class="mb-0">Dataset Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('datasets.store') }}" id="datasetForm">
                    @csrf
                    <input type="hidden" name="creation_method" value="manual">

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
                                  id="description" name="description" rows="3">{{ old('description') }}"></textarea>
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

        <!-- OCR Creation Form -->
        <div class="card d-none" id="ocrForm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-image"></i> Buat Dataset dari Gambar OCR
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('datasets.store') }}" enctype="multipart/form-data" id="ocrDatasetForm">
                    @csrf
                    <input type="hidden" name="creation_method" value="ocr">

                    <div class="mb-3">
                        <label for="ocr_name" class="form-label">Dataset Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="ocr_name" name="name" value="{{ old('name', 'Data OCR ' . date('d-m-Y')) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ocr_description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="ocr_description" name="description" rows="3">{{ old('description') }}"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- OCR Image Upload -->
                    <div class="mb-3">
                        <label for="ocr_image" class="form-label">
                            <strong>Upload Gambar Tabel *</strong>
                        </label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                               id="ocr_image" name="image" accept="image/*" required>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Upload gambar yang berisi tabel. Sistem akan otomatis mendeteksi kolom dan data.
                        </div>
                    </div>

                    <!-- OCR Options -->
                    <div class="mb-3">
                        <label class="form-label">
                            <strong>Opsi OCR</strong>
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="has_header" name="has_header" checked>
                            <label class="form-check-label" for="has_header">
                                Gambar memiliki header (baris pertama adalah nama kolom)
                            </label>
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
                                <table class="table table-sm table-bordered">
                                    <thead id="ocrPreviewTableHead"></thead>
                                    <tbody id="ocrPreviewTableBody"></tbody>
                                </table>
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="fas fa-info-circle"></i> Menampilkan 5 baris pertama dari total <span id="ocrTotalRows">0</span> baris yang terdeteksi
                            </div>
                        </div>
                    </div>

                    <!-- OCR Processing Overlay -->
                    <div id="ocrProcessing" class="d-none">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h4 class="mt-3">Memproses OCR...</h4>
                            <p class="text-muted">Sedang membaca tabel dari gambar</p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('datasets.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-success" id="submitOcrBtn" disabled>
                            <i class="fas fa-cogs"></i> Proses OCR & Buat Dataset
                        </button>
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
                <ul id="manualTips">
                    <li>Give your dataset a descriptive name</li>
                    <li>Add columns that represent your data structure</li>
                    <li>You can always add more columns later</li>
                    <li>Example columns: Name, Email, Age, etc.</li>
                </ul>
                <ul id="ocrTips" style="display: none;">
                    <li>Pastikan gambar tabel jelas dan terbaca</li>
                    <li>Gambar harus memiliki kontras yang baik</li>
                    <li>Tabel dengan garis batas akan lebih akurat dideteksi</li>
                    <li>Ukuran file maksimal 5MB</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form switching logic
    const manualRadio = document.getElementById('manual');
    const ocrRadio = document.getElementById('ocr');
    const manualForm = document.getElementById('manualForm');
    const ocrForm = document.getElementById('ocrForm');
    const manualTips = document.getElementById('manualTips');
    const ocrTips = document.getElementById('ocrTips');

    function switchForm(method) {
        if (method === 'manual') {
            manualForm.classList.remove('d-none');
            ocrForm.classList.add('d-none');
            manualTips.style.display = 'block';
            ocrTips.style.display = 'none';
        } else {
            manualForm.classList.add('d-none');
            ocrForm.classList.remove('d-none');
            manualTips.style.display = 'none';
            ocrTips.style.display = 'block';
        }
    }

    manualRadio.addEventListener('change', function() {
        if (this.checked) switchForm('manual');
    });

    ocrRadio.addEventListener('change', function() {
        if (this.checked) switchForm('ocr');
    });

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

    // OCR functionality
    let currentImage = null;
    const ocrImageInput = document.getElementById('ocr_image');
    const submitOcrBtn = document.getElementById('submitOcrBtn');

    ocrImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            currentImage = file;
            processOcrPreview(file);
        } else {
            currentImage = null;
            hideOcrPreview();
            submitOcrBtn.disabled = true;
        }
    });

    function processOcrPreview(file) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('has_header', document.getElementById('has_header').checked);

        // Show processing
        document.getElementById('ocrProcessing').classList.remove('d-none');
        document.getElementById('ocrPreviewSection').style.display = 'none';

        fetch('{{ route("datasets.previewOcr") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('ocrProcessing').classList.add('d-none');

            if (data.success) {
                updateOcrPreview(data);
                submitOcrBtn.disabled = false;
            } else {
                showError('Gagal memproses OCR: ' + data.message);
                submitOcrBtn.disabled = true;
            }
        })
        .catch(error => {
            document.getElementById('ocrProcessing').classList.add('d-none');
            showError('Error: ' + error.message);
            submitOcrBtn.disabled = true;
        });
    }

    function updateOcrPreview(data) {
        // Update info badge
        const headerText = data.has_header ? 'dengan Header' : 'tanpa Header';
        document.getElementById('ocrPreviewInfo').innerHTML =
            `${data.columns.length} kolom, ${data.total_rows} baris (${headerText})`;
        document.getElementById('ocrTotalRows').textContent = data.total_rows;

        // Display columns as badges
        const columnsList = document.getElementById('ocrColumnsList');
        columnsList.innerHTML = '';
        data.columns.forEach((col, index) => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary';
            badge.textContent = col;
            columnsList.appendChild(badge);
        });

        // Display table header
        const tableHead = document.getElementById('ocrPreviewTableHead');
        tableHead.innerHTML = '';
        const headerRow = document.createElement('tr');
        data.columns.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col;
            headerRow.appendChild(th);
        });
        tableHead.appendChild(headerRow);

        // Display table body
        const tableBody = document.getElementById('ocrPreviewTableBody');
        tableBody.innerHTML = '';
        data.preview.forEach((row, rowIndex) => {
            const tr = document.createElement('tr');
            data.columns.forEach((col, colIndex) => {
                const td = document.createElement('td');
                td.textContent = row[colIndex] || '';
                tr.appendChild(td);
            });
            tableBody.appendChild(tr);
        });

        // Show preview section
        document.getElementById('ocrPreviewSection').style.display = 'block';
    }

    function hideOcrPreview() {
        document.getElementById('ocrPreviewSection').style.display = 'none';
        document.getElementById('ocrProcessing').classList.add('d-none');
    }

    function showError(message) {
        // You can implement a better error display
        alert(message);
    }

    // Handle form submission for OCR
    document.getElementById('ocrDatasetForm').addEventListener('submit', function(e) {
        if (!currentImage) {
            e.preventDefault();
            showError('Silakan pilih gambar terlebih dahulu');
            return;
        }

        // Show processing overlay
        document.getElementById('ocrProcessing').classList.remove('d-none');
    });
});
</script>
@endsection
