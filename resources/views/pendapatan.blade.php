{{-- pendapatan_barber.blade.php --}}
@extends('layouts.app')

@section('title', 'Laporan Pendapatan | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Pendapatan Barber</h5>
        <form method="GET" action="{{ route('pendapatan') }}" class="row g-2 mb-3 justify-content-end">
            <div class="col-md-3">
                <label for="barber" class="form-label mb-0 small">Filter Barber</label>
                <select name="barber" id="barber" class="form-select form-select-sm">
                    <option value="">-- Semua Barber --</option>
                    @foreach ($barbers as $barber)
                        <option value="{{ $barber->id }}" {{ request('barber') == $barber->id ? 'selected' : '' }}>
                            {{ $barber->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="bulan" class="form-label mb-0 small">Filter Bulan</label>
                <input type="month" name="bulan" id="bulan" class="form-control form-control-sm" value="{{ request('bulan') }}">
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('pendapatan') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>ID Pendapatan</th>
                        <th>Nama Barber</th>
                        <th>Nama Pelanggan</th>
                        <th>Tanggal Jadwal</th>
                        <th>Nominal Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendapatan as $index => $item)
                        <tr>
                            <td>{{ $item->id_pesanan }}</td>
                            <td>
                                {{-- Menggunakan ID pendapatan yang di-generate otomatis --}}
                                {{ $item->id_pendapatan_generated ?? 'PD' . ($index + 1) }}
                            </td>
                            <td>
                                @if($item->barber)
                                    {{ $item->barber->nama }}
                                @else
                                    <span class="text-muted">Barber tidak ditemukan</span>
                                @endif
                            </td>
                            <td>
                                @if($item->pelanggan)
                                    {{ $item->pelanggan->nama }}
                                @else
                                    <span class="text-muted">Pelanggan tidak ditemukan</span>
                                @endif
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('d M Y') }}
                            </td>
                            <td>Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data pendapatan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Info total --}}
        @if($pendapatan->count() > 0)
        <div class="mt-3">
            <strong>Total Pendapatan: Rp {{ number_format($pendapatan->sum('total_bayar'), 0, ',', '.') }}</strong>
        </div>
        @endif
    </div>
</div>
@endsection
