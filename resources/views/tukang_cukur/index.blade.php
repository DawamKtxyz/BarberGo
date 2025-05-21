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
                        <th width="150">Aksi</th>
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
                                <a href="{{ route('tukang_cukur.show', $barber->id) }}" class="btn btn-info btn-sm mb-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('tukang_cukur.edit', $barber->id) }}" class="btn btn-primary btn-sm mb-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('tukang_cukur.destroy', $barber->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm mb-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data tukang cukur.</td>
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
@endsection
