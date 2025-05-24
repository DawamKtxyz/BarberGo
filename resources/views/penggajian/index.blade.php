@extends('layouts.app')

@section('title', 'Daftar Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penggajian</h5>
        <div class="btn-group">
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-plus"></i> Generate Gaji
            </button>
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
                        <th>Nama Barber</th>
                        <th>Nama Pelanggan</th>
                        <th>Tanggal Pesanan</th>
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
                            <td>{{ $p->nama_barber }}</td>
                            <td>{{ $p->nama_pelanggan }}</td>
                            <td>{{ $p->tanggal_pesanan->format('d/m/Y H:i') }}</td>
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
                                    <a href="{{ route('penggajian.edit', $p->id_gaji) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('penggajian.destroy', $p->id_gaji) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @if($p->bukti_transfer)
                                        <a href="{{ Storage::url($p->bukti_transfer) }}" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fas fa-image"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada data penggajian</td>
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

<!-- Modal Generate Gaji -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('penggajian.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Data Penggajian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Barber (Opsional)</label>
                        <select name="id_barber" class="form-select">
                            <option value="">Semua Barber</option>
                            @foreach(App\Models\TukangCukur::all() as $barber)
                                <option value="{{ $barber->id }}">{{ $barber->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bayar Gaji -->
<div class="modal fade" id="bayarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('penggajian.bayar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bayar Gaji</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="selected-items">
                        <!-- Akan diisi dengan JavaScript -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Transfer</label>
                        <input type="file" name="bukti_transfer" class="form-control" accept="image/*" required>
                        <small class="text-muted">Upload foto bukti transfer</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Bayar</button>
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

    // Select All functionality
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = this.checked;
            }
        });
        updateBayarButton();
    });

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBayarButton);
    });

    function updateBayarButton() {
        const checkedBoxes = document.querySelectorAll('.gaji-checkbox:checked');
        btnBayar.disabled = checkedBoxes.length === 0;

        // Update selected items in modal
        const selectedItems = document.getElementById('selected-items');
        selectedItems.innerHTML = '';

        checkedBoxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'id_gaji[]';
            hiddenInput.value = checkbox.value;
            selectedItems.appendChild(hiddenInput);
        });
    }
});
</script>
@endpush
@endsection
