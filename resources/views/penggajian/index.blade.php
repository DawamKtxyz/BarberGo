@extends('layouts.app')

@section('title', 'Daftar Penggajian | Panel Admin')

@push('styles')
<style>
    /* Modal styling for bayar modal only */
    .modal {
        z-index: 9999 !important;
    }

    .modal-backdrop {
        z-index: 9998 !important;
    }

    .modal-dialog {
        z-index: 10000 !important;
        position: relative;
    }

    .modal-content {
        z-index: 10001 !important;
        position: relative;
    }

    /* Force lower z-index for other elements when modal is open */
    body.modal-open .navbar {
        z-index: 1030 !important;
    }

    body.modal-open .sidebar {
        z-index: 1000 !important;
    }

    body.modal-open .content {
        z-index: 1000 !important;
    }

    /* Ensure modal is clickable and interactive */
    .modal.show {
        display: block !important;
        pointer-events: auto !important;
    }

    .modal-dialog {
        pointer-events: auto !important;
    }

    .modal-content {
        pointer-events: auto !important;
    }

    /* Additional styling for better UX */
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    /* Modal styling */
    .modal-content {
        box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
        border: 2px solid #007bff !important;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penggajian</h5>
        <div class="btn-group">
            <a href="{{ route('penggajian.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Generate Gaji
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bayarModal" id="btnBayar" disabled>
                <i class="fas fa-money-bill"></i> Bayar Gaji
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('penggajian.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nama Barber</label>
                    <select name="nama_barber" class="form-select form-select-sm">
                        <option value="">Semua Barber</option>
                        @foreach($barbers as $nama)
                            <option value="{{ $nama }}" {{ request('nama_barber') == $nama ? 'selected' : '' }}>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" class="form-control form-control-sm" value="{{ request('tanggal_dari') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control form-control-sm" value="{{ request('tanggal_sampai') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-info btn-sm me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('penggajian.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>No</th>
                        <th>ID Pesanan</th>
                        <th>Nama Barber</th>
                        <th>Nama Pelanggan</th>
                        <th>Tanggal Jadwal</th>
                        <th>Jam Jadwal</th>
                        <th>Total Bayar</th>
                        <th>Potongan (5%)</th>
                        <th>Total Gaji</th>
                        <th>Rekening</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penggajian as $index => $p)
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_gaji[]" value="{{ $p->id_gaji }}"
                                       class="form-check-input gaji-checkbox"
                                       {{ $p->status == 'lunas' ? 'disabled' : '' }}>
                            </td>
                            <td>{{ $index + $penggajian->firstItem() }}</td>
                            <td>
                                <span class="badge bg-info">#{{ $p->id_pesanan }}</span>
                            </td>
                            <td>{{ $p->nama_barber }}</td>
                            <td>{{ $p->nama_pelanggan }}</td>
                            <td>{{ $p->tanggal_jadwal }}</td>
                            <td>{{ $p->jam_jadwal }}</td>
                            <td>Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($p->potongan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($p->total_gaji, 0, ',', '.') }}</td>
                            <td>{{ $p->rekening_barber }}</td>
                            <td>
                                @if($p->status == 'lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-warning">Belum Lunas</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('penggajian.edit', $p->id_gaji) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('penggajian.destroy', $p->id_gaji) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @if($p->bukti_transfer)
                                        <a href="{{ Storage::url($p->bukti_transfer) }}" target="_blank" class="btn btn-info btn-sm" title="Lihat Bukti Transfer">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">Tidak ada data penggajian</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $penggajian->links() }}
        </div>
    </div>
</div>

<!-- Modal Bayar Gaji -->
<div class="modal fade" id="bayarModal" tabindex="-1" aria-labelledby="bayarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('penggajian.bayar') }}" method="POST" enctype="multipart/form-data" id="bayarForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bayarModalLabel">Bayar Gaji</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="selected-items">
                        <!-- Akan diisi dengan JavaScript -->
                    </div>
                    <div id="selected-summary" class="mb-3">
                        <!-- Summary akan ditampilkan di sini -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Transfer <span class="text-danger">*</span></label>
                        <input type="file" name="bukti_transfer" class="form-control" accept="image/*" required>
                        <small class="text-muted">Upload foto bukti transfer (JPG, PNG, max 2MB)</small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Pastikan bukti transfer sudah benar sebelum mengirim. Status akan berubah menjadi "Lunas" setelah pembayaran.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-spinner fa-spin" id="bayarSpinner" style="display: none;"></i>
                        <span id="bayarText">Bayar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.gaji-checkbox');
    const btnBayar = document.getElementById('btnBayar');
    const bayarForm = document.getElementById('bayarForm');

    // Modal handling for bayar modal only
    const bayarModal = document.getElementById('bayarModal');

    if (bayarModal) {
        bayarModal.addEventListener('show.bs.modal', function () {
            console.log('Bayar modal showing...');
            document.body.style.overflow = 'hidden';
            this.style.zIndex = '9999';
            this.style.display = 'block';
            this.style.pointerEvents = 'auto';

            const modalContent = this.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.zIndex = '10001';
                modalContent.style.pointerEvents = 'auto';
                modalContent.style.position = 'relative';
            }
        });

        bayarModal.addEventListener('hidden.bs.modal', function () {
            console.log('Bayar modal hidden');
            document.body.style.overflow = '';
        });
    }

    // Select All functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = this.checked;
                }
            });
            updateBayarButton();
        });
    }

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBayarButton();
            updateSelectAllState();
        });
    });

    function updateSelectAllState() {
        const enabledCheckboxes = Array.from(checkboxes).filter(cb => !cb.disabled);
        const checkedEnabledBoxes = enabledCheckboxes.filter(cb => cb.checked);

        if (selectAll) {
            selectAll.checked = enabledCheckboxes.length > 0 && checkedEnabledBoxes.length === enabledCheckboxes.length;
            selectAll.indeterminate = checkedEnabledBoxes.length > 0 && checkedEnabledBoxes.length < enabledCheckboxes.length;
        }
    }

    function updateBayarButton() {
        const checkedBoxes = document.querySelectorAll('.gaji-checkbox:checked');
        if (btnBayar) {
            btnBayar.disabled = checkedBoxes.length === 0;
        }

        // Update selected items in modal
        const selectedItems = document.getElementById('selected-items');
        const selectedSummary = document.getElementById('selected-summary');
        if (selectedItems) selectedItems.innerHTML = '';
        if (selectedSummary) selectedSummary.innerHTML = '';

        if (checkedBoxes.length > 0) {
            let totalGaji = 0;
            let summaryHTML = '<div class="alert alert-info"><strong>Data yang akan dibayar:</strong><ul class="mb-0 mt-2">';

            checkedBoxes.forEach(checkbox => {
                // Hidden input untuk form
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'id_gaji[]';
                hiddenInput.value = checkbox.value;
                if (selectedItems) selectedItems.appendChild(hiddenInput);

                // Get data from table row
                const row = checkbox.closest('tr');
                const namaBarber = row.cells[3].textContent.trim();
                const namaPelanggan = row.cells[4].textContent.trim();
                const totalGajiText = row.cells[8].textContent.trim();
                const totalGajiValue = parseInt(totalGajiText.replace(/[^\d]/g, ''));
                totalGaji += totalGajiValue;

                summaryHTML += `<li>${namaBarber} - ${namaPelanggan}: ${totalGajiText}</li>`;
            });

            summaryHTML += `</ul><hr class="my-2"><strong>Total Pembayaran: Rp ${new Intl.NumberFormat('id-ID').format(totalGaji)}</strong></div>`;
            if (selectedSummary) selectedSummary.innerHTML = summaryHTML;
        }
    }

    // Form validation and loading states for bayar form
    if (bayarForm) {
        bayarForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('bayarSpinner');
            const text = document.getElementById('bayarText');

            if (submitBtn) submitBtn.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (text) text.textContent = 'Processing...';

            // Re-enable after 10 seconds (fallback)
            setTimeout(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.style.display = 'none';
                if (text) text.textContent = 'Bayar';
            }, 10000);
        });
    }

    // File size validation
    if (bayarForm) {
        const fileInput = bayarForm.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (file.size > maxSize) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB.');
                        this.value = '';
                        return;
                    }

                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!validTypes.includes(file.type)) {
                        alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
                        this.value = '';
                        return;
                    }
                }
            });
        }
    }

    // Initialize states
    updateBayarButton();
    updateSelectAllState();
});

// Modal trigger handling for bayar modal only
document.addEventListener('click', function(e) {
    if (e.target.hasAttribute('data-bs-toggle') && e.target.getAttribute('data-bs-toggle') === 'modal') {
        console.log('Modal trigger clicked:', e.target);
        const targetModal = document.querySelector(e.target.getAttribute('data-bs-target'));
        if (targetModal && targetModal.id === 'bayarModal') {
            console.log('Target modal found:', targetModal);
            // Force show the modal
            setTimeout(() => {
                targetModal.style.display = 'block';
                targetModal.classList.add('show');
                document.body.classList.add('modal-open');

                // Create backdrop if it doesn't exist
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.style.zIndex = '9998';
                    document.body.appendChild(backdrop);
                }
            }, 10);
        }
    }
});
</script>
@endpush
@endsection
