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
                            <th>Tanggal & Jam</th>
                            <th>Harga</th>
                            <th>Ongkir</th>
                            <th>Total</th>
                            <th>Kode Transaksi</th>
                            <th>Status</th>
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
                                <td>
                                    @if($l->jadwal)
                                        <div>{{ $l->tanggal_jadwal_format }}</div>
                                        <small class="text-muted">{{ $l->jam_jadwal_format }}</small>
                                    @else
                                        {{ $l->tgl_pesanan }}
                                    @endif
                                </td>
                                <td>Rp {{ number_format($l->nominal, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($l->ongkos_kirim ?? 10000, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format(($l->nominal + ($l->ongkos_kirim ?? 10000)), 0, ',', '.') }}</td>
                                <td>{{ $l->id_transaksi ?? '-' }}</td>
                                <td>
                                    @if($l->status_pembayaran == 'pending')
                                        <span class="badge bg-warning">Menunggu Pembayaran</span>
                                    @elseif($l->status_pembayaran == 'paid')
                                        <span class="badge bg-success">Sudah Dibayar</span>
                                    @elseif($l->status_pembayaran == 'failed')
                                        <span class="badge bg-danger">Gagal</span>
                                    @elseif($l->status_pembayaran == 'expired')
                                        <span class="badge bg-secondary">Kedaluwarsa</span>
                                    @else
                                        <span class="badge bg-info">{{ ucfirst($l->status_pembayaran ?? 'Tidak Diketahui') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        {{-- Tombol Pembayaran --}}
                                        <a href="{{ route('pembayaran.show', $l->id) }}" class="btn btn-success btn-sm" title="Lihat Pembayaran">
                                            <i class="fas fa-credit-card"></i>
                                        </a>

                                        {{-- Tombol Detail --}}
                                        <a href="{{ route('pesanan.show', $l->id) }}" class="btn btn-info btn-sm" title="Detail Pesanan">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('pesanan.edit', $l->id) }}" class="btn btn-warning btn-sm" title="Edit Pesanan">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('pesanan.destroy', $l->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Pesanan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center">Tidak ada data pesanan</td>
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

@section('styles')
<style>
    .btn-group .btn {
        margin-right: 2px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endsection
