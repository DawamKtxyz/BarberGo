@extends('layouts.app')

@section('title', 'Daftar Tukang Cukur | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Tukang Cukur</h5>
        <a href="{{ route('tukang_cukur.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Tukang Cukur
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Spesialisasi</th>
                        <th>Tarif</th>
                        <th>Status</th>
                        <th>Sertifikat</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tukangCukur as $index => $barber)
                        <tr>
                            <td>{{ $tukangCukur->firstItem() + $index }}</td>
                            <td>{{ $barber->nama }}</td>
                            <td>{{ $barber->email }}</td>
                            <td>{{ $barber->telepon }}</td>
                            <td>{{ $barber->spesialisasi ?: '-' }}</td>
                            <td>Rp {{ number_format($barber->harga, 0, ',', '.') }}</td>
                            <td>
                                @if($barber->is_verified)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock"></i> Menunggu Verifikasi
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($barber->sertifikat)
                                    <a href="{{ asset($barber->sertifikat) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('tukang_cukur.show', $barber->id) }}" class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tukang_cukur.edit', $barber->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($barber->is_verified)
                                        <form action="{{ route('tukang_cukur.unverify', $barber->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-warning btn-sm" title="Batalkan Verifikasi" onclick="return confirm('Apakah Anda yakin ingin membatalkan verifikasi?')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('tukang_cukur.verify', $barber->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-success btn-sm" title="Verifikasi" onclick="return confirm('Apakah Anda yakin ingin memverifikasi tukang cukur ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('tukang_cukur.destroy', $barber->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada data tukang cukur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $tukangCukur->links() }}
        </div>
    </div>
</div>

<style>
    .btn-group .btn {
        margin-right: 2px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endsection
