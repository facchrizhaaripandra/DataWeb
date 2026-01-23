<form id="editRowForm">
    @csrf
    <div class="row">
        @foreach($dataset->columns as $column)
        <div class="col-md-4 mb-3">
            <label for="edit_column_{{ $loop->index }}" class="form-label">
                {{ $column }}
            </label>
            <input type="text" class="form-control" 
                   id="edit_column_{{ $loop->index }}" 
                   name="data[{{ $column }}]" 
                   value="{{ $row->data[$column] ?? '' }}"
                   placeholder="Masukkan {{ $column }}">
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
    const formData = new FormData(document.getElementById('editRowForm'));
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        const match = key.match(/data\[(.+)\]/);
        if (match) {
            data[match[1]] = value;
        }
    }
    
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