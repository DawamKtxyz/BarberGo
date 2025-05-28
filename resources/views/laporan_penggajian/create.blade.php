@extends('layouts.app')

@section('title', 'Tambah Laporan Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Laporan Penggajian</h5>
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

        <form action="{{ route('laporan_penggajian.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="id_barber" class="form-label">Barber <span class="text-danger">*</span></label>
                <select id="id_barber" class="form-control @error('id_barber') is-invalid @enderror" name="id_barber" required>
                    <option value="">-- Pilih Barber --</option>
                    @foreach($barbers as $barber)
                        <option value="{{ $barber->id }}" {{ old('id_barber') == $barber->id ? 'selected' : '' }}>
                            {{ $barber->nama }}
                        </option>
                    @endforeach
                </select>
                @error('id_barber')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="periode_dari" class="form-label">Periode Dari <span class="text-danger">*</span></label>
                        <input type="date" id="periode_dari" class="form-control @error('periode_dari') is-invalid @enderror"
                               name="periode_dari" value="{{ old('periode_dari') }}" required>
                        @error('periode_dari')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="periode_sampai" class="form-label">Periode Sampai <span class="text-danger">*</span></label>
                        <input type="date" id="periode_sampai" class="form-control @error('periode_sampai') is-invalid @enderror"
                               name="periode_sampai" value="{{ old('periode_sampai') }}" required>
                        @error('periode_sampai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Informasi Perhitungan:</strong>
                <ul class="mb-0 mt-2">
                    <li>Sistem akan otomatis menghitung jumlah pesanan dan pelanggan berdasarkan periode yang dipilih</li>
                    <li><strong>Total Pendapatan</strong> = Jumlah (Nominal Pesanan + Ongkos Kirim Rp 10.000)</li>
                    <li><strong>Potongan Komisi</strong> = 5% per pesanan (contoh: 2 pesanan = 10%)</li>
                    <li><strong>Total Gaji</strong> = Total Pendapatan - Potongan Komisi</li>
                </ul>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Generate Laporan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Form Generate Laporan Bulanan --}}
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Generate Laporan Bulanan</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('laporan_penggajian.generate_bulanan') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="bulan" class="form-label">Bulan <span class="text-danger">*</span></label>
                        <input type="month" id="bulan" class="form-control" name="bulan" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="id_barber_bulanan" class="form-label">Barber (Opsional)</label>
                        <select id="id_barber_bulanan" class="form-control" name="id_barber">
                            <option value="">-- Semua Barber --</option>
                            @foreach($barbers as $barber)
                                <option value="{{ $barber->id }}">{{ $barber->nama }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kosongkan untuk generate semua barber</small>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perhatian:</strong> Generate bulanan akan membuat laporan untuk seluruh bulan (tanggal 1 sampai akhir bulan).
                Jika laporan untuk periode tersebut sudah ada, maka akan dilewati.
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-calendar-alt"></i> Generate Laporan Bulanan
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set default bulan ke bulan sebelumnya
    document.addEventListener('DOMContentLoaded', function() {
        const bulanInput = document.getElementById('bulan');
        const currentDate = new Date();
        currentDate.setMonth(currentDate.getMonth() - 1); // Bulan sebelumnya

        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');

        bulanInput.value = `${year}-${month}`;
    });

    // Validasi periode
    document.getElementById('periode_sampai').addEventListener('change', function() {
        const periodeDari = document.getElementById('periode_dari').value;
        const periodeSampai = this.value;

        if (periodeDari && periodeSampai && periodeSampai < periodeDari) {
            alert('Periode sampai harus setelah atau sama dengan periode dari');
            this.value = '';
        }
    });
</script>
@endpush
