@extends('layouts.app')

@section('title', 'Edit Laporan Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Laporan Penggajian</h5>
        <a href="{{ route('laporan_penggajian.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('laporan_penggajian.update', $laporanPenggajian) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="id_barber" class="form-label">Barber <span class="text-danger">*</span></label>
                <select id="id_barber" class="form-control @error('id_barber') is-invalid @enderror" name="id_barber" required>
                    <option value="">-- Pilih Barber --</option>
                    @foreach($barbers as $barber)
                        <option value="{{ $barber->id }}" {{ old('id_barber', $laporanPenggajian->id_barber) == $barber->id ? 'selected' : '' }}>
                            {{ $barber->nama }}
                        </option>
                    @endforeach
                </select>
                @error('id_barber')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="id_pesanan" class="form-label">Pesanan <span class="text-danger">*</span></label>
                <select id="id_pesanan" class="form-control @error('id_pesanan') is-invalid @enderror" name="id_pesanan" required>
                    <option value="">-- Pilih Pesanan --</option>
                    @foreach($pesanans as $pesanan)
                        <option value="{{ $pesanan->id }}" {{ old('id_pesanan', $laporanPenggajian->id_pesanan) == $pesanan->id ? 'selected' : '' }}>
                            {{ $pesanan->id_transaksi }} - {{ $pesanan->pelanggan->nama ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                @error('id_pesanan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="Belum Dibayar" {{ old('status', $laporanPenggajian->status) == 'Belum Dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="Dibayar" {{ old('status', $laporanPenggajian->status) == 'Dibayar' ? 'selected' : '' }}>Dibayar</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Perbarui</button>
            </div>
        </form>
    </div>
</div>
@endsection