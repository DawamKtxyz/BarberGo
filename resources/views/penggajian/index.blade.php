@extends('layouts.app')

@section('title', 'Daftar Penggajian | Panel Admin')

@push('styles')
<style>
    /* Hapus semua styling modal karena tidak digunakan lagi */
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

    /* TAMBAHAN: Style untuk kolom bank */
    .bank-info {
        font-size: 0.85rem;
        color: #495057;
    }

    .bank-name {
        font-weight: 600;
        color: #28a745;
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
            <button type="button" class="btn btn-primary btn-sm" id="btnBayar" disabled onclick="redirectToBayarGaji()">
                <i class="fas fa-money-bill"></i> Bayar Gaji
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Form - TIDAK BERUBAH -->
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

        <!-- Tabel - PERUBAHAN: Tambah kolom Nama Bank -->
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
                        <th>Nama Bank</th> <!-- TAMBAHAN: Kolom nama bank -->
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
                            <!-- TAMBAHAN: Tampilkan nama bank -->
                            <td>
                                <span class="bank-name">{{ $p->nama_bank ?? 'Tidak Ada' }}</span>
                            </td>
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
                            <td colspan="14" class="text-center">Tidak ada data penggajian</td> <!-- PERUBAHAN: colspan dari 12 ke 14 -->
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.gaji-checkbox');
    const btnBayar = document.getElementById('btnBayar');

    // Select All functionality - TIDAK BERUBAH
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

    // Individual checkbox change - TIDAK BERUBAH
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
    }

    // Initialize states
    updateBayarButton();
    updateSelectAllState();
});

// PERUBAHAN: Fungsi baru untuk redirect ke halaman bayar gaji
function redirectToBayarGaji() {
    const checkedBoxes = document.querySelectorAll('.gaji-checkbox:checked');

    if (checkedBoxes.length === 0) {
        alert('Pilih minimal satu data gaji untuk dibayar!');
        return;
    }

    // Kumpulkan ID gaji yang dipilih
    const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

    // Redirect ke halaman bayar gaji dengan parameter ID
    const url = "{{ route('penggajian.bayar.form') }}" + '?ids=' + selectedIds.join(',');
    window.location.href = url;
}
</script>
@endpush
@endsection
