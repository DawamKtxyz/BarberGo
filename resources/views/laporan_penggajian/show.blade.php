@extends('layouts.app')

@section('title', 'Detail Laporan Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Laporan Penggajian</h5>
        <div>
            <a href="{{ route('laporan_penggajian.edit', $laporan->id_gaji) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('laporan_penggajian.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Informasi Umum --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Informasi Barber</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>ID Barber:</strong></td>
                                <td>{{ $laporan->id_barber }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama Barber:</strong></td>
                                <td>{{ $laporan->nama_barber }}</td>
                            </tr>
                            <tr>
                                <td><strong>Periode:</strong></td>
                                <td>
                                    {{ \Carbon\Carbon::parse($laporan->periode_dari)->format('d/m/Y') }} -
                                    {{ \Carbon\Carbon::parse($laporan->periode_sampai)->format('d/m/Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($laporan->status == 'Dibayar')
                                        <span class="badge bg-success">{{ $laporan->status }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $laporan->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Statistik Kerja</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Jumlah Pesanan:</strong></td>
                                <td><span class="badge bg-info">{{ $laporan->jumlah_pesanan }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Pelanggan:</strong></td>
                                <td><span class="badge bg-secondary">{{ $laporan->jumlah_pelanggan }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Rata-rata per Pesanan:</strong></td>
                                <td>
                                    @if($laporan->jumlah_pesanan > 0)
                                        Rp {{ number_format($laporan->total_pendapatan / $laporan->jumlah_pesanan, 0, ',', '.') }}
                                    @else
                                        Rp 0
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat:</strong></td>
                                <td>{{ $laporan->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Keuangan --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">Rp {{ number_format($laporan->total_pendapatan, 0, ',', '.') }}</h3>
                        <small>Total Pendapatan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">Rp {{ number_format($laporan->potongan_komisi, 0, ',', '.') }}</h3>
                        <small>Potongan Komisi (5%)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">Rp {{ number_format($laporan->total_gaji, 0, ',', '.') }}</h3>
                        <small>Total Gaji</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Pesanan --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Detail Pesanan dalam Periode</h6>
            </div>
            <div class="card-body">
                @if($laporan->detailLaporan && $laporan->detailLaporan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>ID Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Tanggal Pesanan</th>
                                    <th>Nominal Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laporan->detailLaporan as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <code>{{ $detail->id_pesanan }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $detail->nama_pelanggan }}</strong>
                                            <br>
                                            <small class="text-muted">ID: {{ $detail->id_pelanggan }}</small>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($detail->tanggal_pesanan)->format('d/m/Y') }}</td>
                                        <td>
                                            <strong>Rp {{ number_format($detail->nominal_bayar, 0, ',', '.') }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th>
                                        <strong>Rp {{ number_format($laporan->detailLaporan->sum('nominal_bayar'), 0, ',', '.') }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Tidak ada detail pesanan yang tersedia</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Riwayat Perubahan --}}
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Informasi Tambahan</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Dibuat:</strong> {{ $laporan->created_at->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Terakhir Diupdate:</strong> {{ $laporan->updated_at->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                </div>

                @if($laporan->status == 'Belum Dibayar')
                    <div class="alert alert-warning mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Gaji untuk periode ini belum dibayarkan kepada barber.
                    </div>
                @else
                    <div class="alert alert-success mt-3" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>Informasi:</strong> Gaji untuk periode ini sudah dibayarkan kepada barber.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
