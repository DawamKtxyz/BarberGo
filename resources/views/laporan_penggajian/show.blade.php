@extends('layouts.app')

@section('title', 'Detail Laporan Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Laporan Penggajian</h5>
        <a href="{{ route('laporan_penggajian.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr>
                    <th width="200">ID</th>
                    <td>{{ $laporanPenggajian->id }}</td>
                </tr>
                <tr>
                    <th>Nama Barber</th>
                    <td>{{ $laporanPenggajian->barber->nama ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>ID Pesanan</th>
                    <td>{{ $laporanPenggajian->id_pesanan }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $laporanPenggajian->status }}</td>
                </tr>
                <tr>
                    <th>Tanggal Dibuat</th>
                    <td>{{ $laporanPenggajian->created_at->format('d-m-Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Terakhir Diupdate</th>
                    <td>{{ $laporanPenggajian->updated_at->format('d-m-Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <div class="mt-3">
            <a href="{{ route('laporan_penggajian.edit', $laporanPenggajian) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('laporan_penggajian.destroy', $laporanPenggajian) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection