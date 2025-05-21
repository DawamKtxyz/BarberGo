@extends('layouts.app')

@section('title', 'Laporan Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Laporan Penggajian</h5>
        {{-- <a href="{{ route('laporan_penggajian.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Laporan
        </a> --}}
        <a href="{{ route('laporan_penggajian.cetak', ['bulan' => request('bulan')]) }}" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf"></i> Cetak PDF
        </a>        
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('laporan_penggajian.index') }}" class="row g-3 mb-3">
            <div class="col-md-2 align-self-end">
                <label for="bulan" class="form-label mb-0 small">Filter Bulan</label>
                <input type="month" name="bulan" id="bulan" class="form-control form-control-sm" value="{{ request('bulan') }}">
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('laporan_penggajian.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Gaji</th> {{-- primarykey --}}
                        <th>ID Pendapatan</th>
                        <th>Nama Barber</th>
                        <th>Jumlah Pelanggan</th>
                        <th>Jumlah Pendapatan</th>
                        <th>Potongan Komisi</th>
                        <th>Total Gaji</th>
                    </tr>
                </thead>
                @php
                    $totalPendapatan = 0;
                    $totalKomisi = 0;
                    $totalGaji = 0;
                 @endphp
                <tbody>
                    @forelse ($laporanPenggajian as $index => $laporan)
                    @php
                        $jumlahPendapatan = $laporan->jumlah_potong * $laporan->tarif_per_potong;
                        $potonganKomisi = $jumlahPendapatan * 0.02;
                        $totalGaji = $jumlahPendapatan - $potonganKomisi;

                        $totalPendapatan += $jumlahPendapatan;
                        $totalKomisi += $potonganKomisi;
                        $totalGaji += $totalGajiItem;
                    @endphp
        
                    <tr>
                        <td>{{ $index + $laporanPenggajian->firstItem() }}</td>
                        <td>{{ $item->id_pendapatan }}</td> 
                        <td>{{ $laporan->barber->nama ?? 'N/A' }}</td>
                        <td>{{ $item->jumlah_potong }}</td>
                        <td>Rp {{ number_format($jumlahPendapatan, 0, ',', '.') }}</td> 
                        <td>Rp {{ number_format($potonganKomisi, 0, ',', '.') }}</td> 
                        <td>Rp {{ number_format($item->total_gaji, 0, ',', '.') }}</td>
                        {{-- <td>{{ $laporan->pesanan->id_transaksi ?? 'N/A' }}</td>
                        <td>{{ $laporan->status }}</td>
                        <td>{{ $laporan->created_at->format('d-m-Y H:i') }}</td> --}}
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('laporan_penggajian.show', $laporan) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('laporan_penggajian.edit', $laporan) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('laporan_penggajian.destroy', $laporan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                        <td colspan="7" class="text-center">Tidak ada data laporan penggajian</td>
                    </tr>
                    @endforelse
                </tbody>
                {{-- Tambahkan Total Keseluruhan --}}
                <tfoot>
                    <tr class="table-success fw-bold">
                        <td colspan="4" class="text-end">Total Keseluruhan:</td>
                        <td>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($totalKomisi, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($totalGaji, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $laporanPenggajian->links() }}
        </div>
    </div>
</div>
@endsection