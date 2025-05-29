@extends('layouts.app')

@section('title', 'Laporan Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Laporan Penggajian</h5>
        <div>
            <a href="{{ route('laporan_penggajian.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Laporan
            </a>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-file-pdf"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('laporan_penggajian.cetak_pdf', request()->query()) }}">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('laporan_penggajian.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="id_barber" class="form-label">Barber</label>
                <select id="id_barber" class="form-control" name="id_barber">
                    <option value="">-- Semua Barber --</option>
                    @foreach($barbers as $barber)
                        <option value="{{ $barber->id }}" {{ request('id_barber') == $barber->id ? 'selected' : '' }}>
                            {{ $barber->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="bulan" class="form-label">Bulan</label>
                <input type="month" id="bulan" class="form-control" name="bulan" value="{{ request('bulan') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" class="form-control" name="status">
                    <option value="">-- Semua Status --</option>
                    <option value="Sudah Digaji" {{ request('status') == 'Sudah Digaji' ? 'selected' : '' }}>Sudah Digaji</option>
                    <option value="Belum Digaji" {{ request('status') == 'Belum Digaji' ? 'selected' : '' }}>Belum Digaji</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('laporan_penggajian.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-refresh"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">Rp {{ number_format($totalKeseluruhan['total_pendapatan'], 0, ',', '.') }}</h4>
                                <small>Total Pendapatan</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">Rp {{ number_format($totalKeseluruhan['total_komisi'], 0, ',', '.') }}</h4>
                                <small>Total Komisi (5% per pesanan)</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-percentage fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">Rp {{ number_format($totalKeseluruhan['total_gaji'], 0, ',', '.') }}</h4>
                                <small>Total Gaji</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-usd fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Barber</th>
                        <th>Periode</th>
                        <th>Jumlah Pesanan</th>
                        <th>Jumlah Pelanggan</th>
                        <th>Total Pendapatan</th>
                        <th>Komisi</th>
                        <th>Total Gaji</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporanPenggajian as $laporan)
                        <tr>
                            <td>{{ ($laporanPenggajian->currentPage() - 1) * $laporanPenggajian->perPage() + $loop->iteration }}</td>
                            <td>
                                <strong>{{ $laporan->nama_barber }}</strong>
                                <br>
                                <small class="text-muted">ID: {{ $laporan->id_barber }}</small>
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($laporan->periode_dari)->format('d/m/Y') }}</small>
                                <br>
                                <small>{{ \Carbon\Carbon::parse($laporan->periode_sampai)->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $laporan->jumlah_pesanan }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $laporan->jumlah_pelanggan }}</span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($laporan->total_pendapatan, 0, ',', '.') }}</strong>
                                <br>
                                <small class="text-muted">{{ $laporan->jumlah_pesanan }} pesanan</small>
                            </td>
                            <td>
                                <span class="text-warning">Rp {{ number_format($laporan->potongan_komisi, 0, ',', '.') }}</span>
                                <br>
                                <small class="text-muted">{{ $laporan->jumlah_pesanan * 5 }}% ({{ $laporan->jumlah_pesanan }} × 5%)</small>
                            </td>
                            <td>
                                <strong class="text-success">Rp {{ number_format($laporan->total_gaji, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($laporan->status_gaji == 'Sudah Digaji')
                                    <span class="badge bg-success">{{ $laporan->status_gaji }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $laporan->status_gaji }}</span>
                                @endif
                                <br>
                                <small class="text-muted">Status dari tabel penggajian</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('laporan_penggajian.show', $laporan->id_gaji) }}"
                                       class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('laporan_penggajian.edit', $laporan->id_gaji) }}"
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="confirmDelete({{ $laporan->id_gaji }})" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Tidak ada data laporan penggajian</p>
                                    <a href="{{ route('laporan_penggajian.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Tambah Laporan Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($laporanPenggajian->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $laporanPenggajian->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus laporan penggajian ini?</p>
                <p class="text-danger"><strong>Perhatian:</strong> Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Info Box untuk Formula Perhitungan --}}
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-calculator"></i> Formula Perhitungan</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="border-start border-primary border-4 ps-3">
                    <h6 class="text-primary">Total Pendapatan</h6>
                    <small class="text-muted">Σ (Nominal Pesanan + Ongkos Kirim Rp 10.000)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-start border-warning border-4 ps-3">
                    <h6 class="text-warning">Komisi</h6>
                    <small class="text-muted">5% × Jumlah Pesanan × Total Pendapatan ÷ 100</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-start border-success border-4 ps-3">
                    <h6 class="text-success">Total Gaji</h6>
                    <small class="text-muted">Total Pendapatan - Komisi</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/laporan-penggajian/${id}`;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endpush
