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
                                        <th>ID Pendapatan</th> {{-- primary key --}}
                                        <th>Nama Barber</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Nominal Bayar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendapatan as $index => $item)
                                        <tr>
                                            <td>{{ $item->pesanan->id ?? '-' }}</td>
                                            <td>
                                                @if($item->pendapatan)
                                                    {{ $item->pendapatan->id_pendapatan }}
                                                @else
                                                    Belum ada pendapatan
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->barber)
                                                    {{ $item->barber->nama }}
                                                @else
                                                    Barber tidak ditemukan
                                                @endif
                                            </td>
                                            <td>{{ $item->pelanggan->nama }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('d M Y') }}</td>
                                            <td>Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
@endsection
{{-- <td>{{ $item->bulan }}</td> --}}
{{-- <td>{{ $item->jumlah_potong }}</td>
<td>Rp {{ number_format($item->gaji_potong, 0, ',', '.') }}</td>
<td>Rp {{ number_format($item->total_gaji, 0, ',', '.') }}</td> --}}
