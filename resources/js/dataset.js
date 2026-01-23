const DatasetModule = (function () {
    // Private variables
    let dataTable = null;
    let selectedRows = new Set();

    // Public methods
    return {
        init: function () {
            this.initDataTable();
            this.initEventListeners();
            this.initSortableColumns();
            this.initInlineEditing();
        },

        initDataTable: function () {
            if ($("#dataTable").length) {
                dataTable = $("#dataTable").DataTable({
                    responsive: true,
                    scrollX: true,
                    scrollY: "70vh",
                    scrollCollapse: true,
                    paging: true,
                    pageLength: 50,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "Semua"],
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    },
                    dom: "Bfrtip",
                    buttons: ["copy", "csv", "excel", "pdf", "print"],
                    columnDefs: [
                        {
                            targets: [0, 1, -1],
                            orderable: false,
                            searchable: false,
                        },
                    ],
                    initComplete: function () {
                        // Re-attach event listeners after initialization
                        DatasetModule.attachEventListeners();
                    },
                    drawCallback: function () {
                        // Update event listeners after each draw
                        DatasetModule.attachEventListeners();
                        DatasetModule.updateSelectedRows();
                    },
                });
            }
        },

        initEventListeners: function () {
            // Select all rows
            $("#selectAllRows").on("change", function () {
                const isChecked = $(this).is(":checked");
                $(".row-checkbox").prop("checked", isChecked);
                DatasetModule.updateSelectedRows();
            });

            // Individual row checkbox
            $(document).on("change", ".row-checkbox", function () {
                DatasetModule.updateSelectedRows();
            });

            // Delete row
            $(document).on("click", ".delete-row", function () {
                const rowId = $(this).data("row-id");
                DatasetModule.deleteRow(rowId);
            });

            // Edit row
            $(document).on("click", ".edit-row", function () {
                const rowId = $(this).data("row-id");
                DatasetModule.editFullRow(rowId);
            });

            // Bulk actions
            $(document).on("click", "#deleteSelectedRows", function () {
                DatasetModule.deleteSelectedRows();
            });

            $(document).on("click", "#duplicateSelectedRows", function () {
                DatasetModule.duplicateSelectedRows();
            });
        },

        attachEventListeners: function () {
            // Re-attach event listeners after DataTable redraw
            $(".delete-row")
                .off("click")
                .on("click", function () {
                    const rowId = $(this).data("row-id");
                    DatasetModule.deleteRow(rowId);
                });

            $(".edit-row")
                .off("click")
                .on("click", function () {
                    const rowId = $(this).data("row-id");
                    DatasetModule.editFullRow(rowId);
                });

            $(".row-checkbox")
                .off("change")
                .on("change", function () {
                    DatasetModule.updateSelectedRows();
                });
        },

        initSortableColumns: function () {
            if ($("#sortableColumns").length) {
                $("#sortableColumns").sortable({
                    placeholder: "ui-state-highlight",
                    update: function (event, ui) {
                        $("#sortableColumns .draggable-column").each(
                            function (index) {
                                $(this)
                                    .find(".badge")
                                    .text(index + 1);
                            },
                        );
                    },
                });
                $("#sortableColumns").disableSelection();
            }
        },

        initInlineEditing: function () {
            $(document).on("dblclick", ".editable-cell", function () {
                const $cell = $(this);
                if ($cell.hasClass("editing")) return;

                const originalValue = $cell.data("original-value") || "";
                const column = $cell.data("column");
                const rowId = $cell.data("row-id");

                $cell.addClass("editing");
                $cell.html(`
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control editable-input" 
                               value="${originalValue}" 
                               data-original="${originalValue}">
                        <button class="btn btn-outline-success btn-save" type="button">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-cancel" type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);

                const $input = $cell.find(".editable-input");
                $input.focus();

                // Handle Enter key
                $input.on("keypress", function (e) {
                    if (e.which === 13) {
                        DatasetModule.saveCellValue($cell, rowId, column);
                    }
                });

                // Handle Escape key
                $input.on("keydown", function (e) {
                    if (e.key === "Escape") {
                        DatasetModule.cancelEdit($cell, originalValue);
                    }
                });

                // Save button click
                $cell.find(".btn-save").on("click", function () {
                    DatasetModule.saveCellValue($cell, rowId, column);
                });

                // Cancel button click
                $cell.find(".btn-cancel").on("click", function () {
                    DatasetModule.cancelEdit($cell, originalValue);
                });
            });
        },

        saveCellValue: function ($cell, rowId, column) {
            const newValue = $cell.find(".editable-input").val();
            const originalValue = $cell
                .find(".editable-input")
                .data("original");

            if (newValue === originalValue) {
                $cell.text(newValue).removeClass("editing");
                return;
            }

            const datasetId = $cell.closest("table").data("dataset-id");

            $.ajax({
                url: `/datasets/${datasetId}/rows/${rowId}`,
                type: "PUT",
                data: {
                    column: column,
                    value: newValue,
                },
                beforeSend: function () {
                    $cell
                        .find(".btn-save")
                        .html('<i class="fas fa-spinner fa-spin"></i>');
                },
                success: function (response) {
                    $cell
                        .text(newValue)
                        .data("original-value", newValue)
                        .removeClass("editing");

                    Swal.fire({
                        icon: "success",
                        title: "Berhasil",
                        text: "Data berhasil diperbarui!",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                },
                error: function (xhr) {
                    $cell
                        .find(".btn-save")
                        .html('<i class="fas fa-check"></i>');
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Gagal memperbarui data",
                    });
                },
            });
        },

        cancelEdit: function ($cell, originalValue) {
            $cell
                .text(originalValue)
                .data("original-value", originalValue)
                .removeClass("editing");
        },

        updateSelectedRows: function () {
            selectedRows.clear();
            $(".row-checkbox:checked").each(function () {
                selectedRows.add($(this).data("row-id"));
            });

            const count = selectedRows.size;
            $("#selectedCount").text(count);

            // Update UI
            $("tbody tr").removeClass("table-primary");
            selectedRows.forEach((rowId) => {
                $("#row-" + rowId).addClass("table-primary");
            });

            // Enable/disable bulk action buttons
            const hasSelection = count > 0;
            $("#deleteSelectedRows").prop("disabled", !hasSelection);
            $("#duplicateSelectedRows").prop("disabled", !hasSelection);
        },

        deleteRow: function (rowId) {
            Swal.fire({
                title: "Hapus Baris?",
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    const datasetId = $("#dataTable").data("dataset-id");

                    $.ajax({
                        url: `/datasets/${datasetId}/rows/${rowId}`,
                        type: "DELETE",
                        success: function (response) {
                            if (dataTable) {
                                dataTable
                                    .row($("#row-" + rowId))
                                    .remove()
                                    .draw();
                            } else {
                                $("#row-" + rowId).remove();
                            }

                            Swal.fire({
                                icon: "success",
                                title: "Terhapus!",
                                text: "Baris berhasil dihapus.",
                                timer: 1500,
                                showConfirmButton: false,
                            });

                            // Update row count
                            const currentCount = parseInt(
                                $(".dataset-stats .badge").text(),
                            );
                            $(".dataset-stats .badge").text(currentCount - 1);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Gagal menghapus baris",
                            });
                        },
                    });
                }
            });
        },

        deleteSelectedRows: function () {
            if (selectedRows.size === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Tidak ada baris dipilih",
                    text: "Pilih baris yang akan dihapus terlebih dahulu",
                });
                return;
            }

            Swal.fire({
                title: "Hapus Baris Terpilih?",
                html: `Anda akan menghapus <strong>${selectedRows.size}</strong> baris.<br>
                       Data yang dihapus tidak dapat dikembalikan!`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    const datasetId = $("#dataTable").data("dataset-id");
                    const rowIds = Array.from(selectedRows);

                    $.ajax({
                        url: `/datasets/${datasetId}/rows/delete-selected`,
                        type: "POST",
                        data: {
                            row_ids: rowIds,
                        },
                        beforeSend: function () {
                            App.showLoading();
                        },
                        success: function (response) {
                            App.hideLoading();

                            // Remove rows from table
                            rowIds.forEach((rowId) => {
                                if (dataTable) {
                                    dataTable.row($("#row-" + rowId)).remove();
                                } else {
                                    $("#row-" + rowId).remove();
                                }
                            });

                            if (dataTable) {
                                dataTable.draw();
                            }

                            // Clear selection
                            selectedRows.clear();
                            $(".row-checkbox").prop("checked", false);
                            DatasetModule.updateSelectedRows();

                            Swal.fire({
                                icon: "success",
                                title: "Berhasil!",
                                text: `${response.count} baris berhasil dihapus.`,
                                timer: 2000,
                                showConfirmButton: false,
                            });

                            // Update row count
                            const currentCount = parseInt(
                                $(".dataset-stats .badge").text(),
                            );
                            $(".dataset-stats .badge").text(
                                currentCount - response.count,
                            );
                        },
                        error: function (xhr) {
                            App.hideLoading();
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Gagal menghapus baris terpilih",
                            });
                        },
                    });
                }
            });
        },

        duplicateSelectedRows: function () {
            if (selectedRows.size === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Tidak ada baris dipilih",
                    text: "Pilih baris yang akan diduplikasi terlebih dahulu",
                });
                return;
            }

            Swal.fire({
                title: "Duplikat Baris?",
                html: `Anda akan menduplikasi <strong>${selectedRows.size}</strong> baris.`,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, duplikasi",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    const datasetId = $("#dataTable").data("dataset-id");
                    const rowIds = Array.from(selectedRows);

                    $.ajax({
                        url: `/datasets/${datasetId}/rows/duplicate`,
                        type: "POST",
                        data: {
                            row_ids: rowIds,
                        },
                        beforeSend: function () {
                            App.showLoading();
                        },
                        success: function (response) {
                            App.hideLoading();

                            Swal.fire({
                                icon: "success",
                                title: "Berhasil!",
                                text: `${response.count} baris berhasil diduplikasi.`,
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            App.hideLoading();
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Gagal menduplikasi baris",
                            });
                        },
                    });
                }
            });
        },

        editFullRow: function (rowId) {
            const datasetId = $("#dataTable").data("dataset-id");

            $.ajax({
                url: `/datasets/${datasetId}/rows/${rowId}/edit-form`,
                type: "GET",
                beforeSend: function () {
                    App.showLoading();
                },
                success: function (response) {
                    App.hideLoading();

                    // Create modal
                    const modalHtml = `
                        <div class="modal fade" id="editRowModal" tabindex="-1">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-edit"></i> Edit Baris
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        ${response.html}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Remove existing modal
                    $("#editRowModal").remove();

                    // Add new modal
                    $("body").append(modalHtml);

                    // Show modal
                    const modal = new bootstrap.Modal(
                        document.getElementById("editRowModal"),
                    );
                    modal.show();

                    // Remove modal on hidden
                    $("#editRowModal").on("hidden.bs.modal", function () {
                        $(this).remove();
                    });
                },
                error: function (xhr) {
                    App.hideLoading();
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Gagal memuat data baris",
                    });
                },
            });
        },

        saveColumnOrder: function () {
            const columns = [];
            $("#sortableColumns .draggable-column").each(function () {
                columns.push($(this).data("column"));
            });

            const datasetId = $("#dataTable").data("dataset-id");

            $.ajax({
                url: `/datasets/${datasetId}/reorder-columns`,
                type: "POST",
                data: {
                    columns: columns,
                },
                beforeSend: function () {
                    App.showLoading();
                },
                success: function (response) {
                    App.hideLoading();
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: "Urutan kolom berhasil disimpan.",
                        timer: 1500,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    App.hideLoading();
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Gagal menyimpan urutan kolom",
                    });
                },
            });
        },
    };
})();

// Make module available globally
window.DatasetModule = DatasetModule;
