@extends('layouts.app')

@section('title', 'Daftar Pesanan | Panel Admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pesanan</h5>
            <a href="{{ route('pesanan.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah pesanan
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Barber</th>
                            <th>Nama Pelanggan</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Tanggal Jadwal</th>
                            <th>Harga</th>
                            <th>Ongkir</th>
                            <th>Total</th>
                            <th>Kode Transaksi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pesanan as $index => $l)
                            <tr>
                                <td>{{ $index + $pesanan->firstItem() }}</td>
                                <td>{{ $l->barber->nama ?? '-' }}</td>
                                <td>{{ $l->pelanggan->nama ?? '-' }}</td>
                                <td>{{ $l->email ?? '-' }}</td>
                                <td>{{ $l->telepon ?? '-' }}</td>
                                <td>{{ $l->alamat_lengkap ?? '-' }}</td>
                                <td>{{ $l->tgl_pesanan }}</td>
                                <td>Rp {{ number_format($l->nominal, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($l->ongkos_kirim ?? 10000, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format(($l->nominal + ($l->ongkos_kirim ?? 10000)), 0, ',', '.') }}</td>
                                <td>{{ $l->id_transaksi ?? '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('pesanan.edit', $l->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('pesanan.destroy', $l->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">Tidak ada data pesanan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $pesanan->links() }}
            </div>
        </div>
    </div>
@endsection
