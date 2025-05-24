@extends('layouts.app')

@section('title', 'Edit Penggajian | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Data Penggajian</h5>
        <a href="{{ route('penggajian.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('penggajian.update', $penggajian->id_gaji) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Barber</label>
                        <input type="text" class="form-control" value="{{ $penggajian->nama_barber }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Pelanggan</label>
                        <input type="text" class="form-control" value="{{ $penggajian->nama_pelanggan }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Pesanan</label>
                        <input type="text" class="form-control" value="{{ $penggajian->tanggal_pesanan->format('d/m/Y H:i') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rekening Barber</label>
                        <input type="text" class="form-control" value="{{ $penggajian->rekening_barber }}" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Total Bayar</label>
                        <input type="text" class="form-control" value="Rp {{ number_format($penggajian->total_bayar, 0, ',', '.') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Potongan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="potongan" class="form-control @error('potongan') is-invalid @enderror"
                                   value="{{ old('potongan', $penggajian->potongan) }}" step="0.01" min="0" required>
                        </div>
                        @error('potongan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Default: 5% dari total bayar (Rp {{ number_format($penggajian->total_bayar * 0.05, 0, ',', '.') }})</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Gaji</label>
                        <input type="text" id="totalGaji" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="belum lunas" {{ old('status', $penggajian->status) == 'belum lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="lunas" {{ old('status', $penggajian->status) == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if($penggajian->bukti_transfer)
                <div class="mb-3">
                    <label class="form-label">Bukti Transfer</label>
                    <div>
                        <img src="{{ Storage::url($penggajian->bukti_transfer) }}" alt="Bukti Transfer"
                             class="img-thumbnail" style="max-width: 300px;">
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalBayar = {{ $penggajian->total_bayar }};
    const potonganInput = document.querySelector('input[name="potongan"]');
    const totalGajiInput = document.getElementById('totalGaji');

    function updateTotalGaji() {
        const potongan = parseFloat(potonganInput.value) || 0;
        const totalGaji = totalBayar - potongan;
        totalGajiInput.value = 'Rp ' + totalGaji.toLocaleString('id-ID');
    }

    potonganInput.addEventListener('input', updateTotalGaji);

    // Update on page load
    updateTotalGaji();
});
</script>
@endpush
@endsection
