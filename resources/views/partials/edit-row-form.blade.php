<form id="editRowForm">
    @csrf
    <div class="row">
        @foreach($dataset->column_definitions as $columnDef)
        <div class="col-md-4 mb-3">
            <label for="edit_column_{{ $loop->index }}" class="form-label">
                {{ $columnDef['name'] }}
                @if(in_array(strtolower($columnDef['name']), ['email', 'e-mail', 'mail']))
                    <span class="text-muted small">(email)</span>
                @elseif(in_array(strtolower($columnDef['name']), ['phone', 'telp', 'telepon', 'hp', 'no hp']))
                    <span class="text-muted small">(telepon)</span>
                @elseif(in_array(strtolower($columnDef['name']), ['date', 'tanggal', 'tgl', 'waktu']))
                    <span class="text-muted small">(tanggal)</span>
                @endif
                <small class="text-muted d-block">{{ ucfirst($columnDef['type']) }}</small>
            </label>
            <input type="{{ $columnDef['type'] === 'date' ? 'date' : ($columnDef['type'] === 'boolean' ? 'checkbox' : 'text') }}"
                   class="form-control"
                   id="edit_column_{{ $loop->index }}"
                   name="data[{{ $columnDef['name'] }}]"
                   value="{{ $columnDef['type'] === 'boolean' ? '1' : ($row->data[$columnDef['name']] ?? '') }}"
                   placeholder="Masukkan {{ $columnDef['name'] }}"
                   {{ $columnDef['type'] === 'boolean' && ($row->data[$columnDef['name']] ?? false) ? 'checked' : '' }}>
        </div>
        @endforeach
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times"></i> Batal
        </button>
        <button type="button" class="btn btn-primary" onclick="updateRow({{ $row->id }})">
            <i class="fas fa-save"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
function updateRow(rowId) {
    const form = document.getElementById('editRowForm');
    const data = {};

    // Get all form inputs
    const inputs = form.querySelectorAll('input[name^="data["]');

    inputs.forEach(input => {
        const match = input.name.match(/data\[(.+)\]/);
        if (match) {
            const columnName = match[1];

            // Handle checkboxes (boolean fields)
            if (input.type === 'checkbox') {
                data[columnName] = input.checked ? '1' : '0';
            } else {
                data[columnName] = input.value;
            }
        }
    });

    $.ajax({
        url: '/datasets/{{ $dataset->id }}/rows/' + rowId,
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            data: data
        },
        success: function(response) {
            $('#editRowModal').modal('hide');
            showSuccess('Baris berhasil diperbarui!');
            setTimeout(() => location.reload(), 1000);
        },
        error: function(xhr) {
            showError('Gagal memperbarui baris');
        }
    });
}
</script>
