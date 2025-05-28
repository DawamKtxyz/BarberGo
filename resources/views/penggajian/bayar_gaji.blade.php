@extends('layouts.app')

@section('title', 'Bayar Gaji | Panel Admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill"></i> Bayar Gaji
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('penggajian.bayar') }}" method="POST" enctype="multipart/form-data" id="bayarForm">
                        @csrf

                        {{-- Data yang akan dibayar --}}
                        <div class="alert alert-info">
                            <h6><strong>Data Gaji yang akan dibayar:</strong></h6>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID Pesanan</th>
                                            <th>Nama Barber</th>
                                            <th>Nama Pelanggan</th>
                                            <th>Tanggal</th>
                                            <th>Total Gaji</th>
                                            <th>Rekening</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalPembayaran = 0; @endphp
                                        @foreach($selectedGaji as $gaji)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info">#{{ $gaji->id_pesanan }}</span>
                                                    <input type="hidden" name="id_gaji[]" value="{{ $gaji->id_gaji }}">
                                                </td>
                                                <td>{{ $gaji->nama_barber }}</td>
                                                <td>{{ $gaji->nama_pelanggan }}</td>
                                                <td>{{ $gaji->tanggal_jadwal }}</td>
                                                <td>Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</td>
                                                <td>{{ $gaji->rekening_barber }}</td>
                                            </tr>
                                            @php $totalPembayaran += $gaji->total_gaji; @endphp
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-warning">
                                        <tr>
                                            <th colspan="4" class="text-end">Total Pembayaran:</th>
                                            <th>Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- Upload bukti transfer --}}
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-image"></i> Bukti Transfer
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="bukti_transfer" class="form-control @error('bukti_transfer') is-invalid @enderror"
                                   accept="image/*" required>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Upload foto bukti transfer (JPG, PNG, maksimal 2MB)
                            </small>
                            @error('bukti_transfer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Peringatan --}}
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Perhatian:</strong>
                            Pastikan bukti transfer sudah benar sebelum mengirim.
                            Status gaji akan berubah menjadi "Lunas" setelah pembayaran berhasil diproses.
                        </div>

                        {{-- Tombol aksi --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('penggajian.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-spinner fa-spin" id="loadingSpinner" style="display: none;"></i>
                                <i class="fas fa-money-bill" id="submitIcon"></i>
                                <span id="submitText">Bayar Gaji</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bayarForm = document.getElementById('bayarForm');
    const fileInput = document.querySelector('input[type="file"]');
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');

    // File validation
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB.');
                    this.value = '';
                    return;
                }

                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
                    this.value = '';
                    return;
                }
            }
        });
    }

    // Form submission handling
    if (bayarForm) {
        bayarForm.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            submitIcon.style.display = 'none';
            submitText.textContent = 'Memproses...';

            // Re-enable after timeout (fallback)
            setTimeout(() => {
                submitBtn.disabled = false;
                loadingSpinner.style.display = 'none';
                submitIcon.style.display = 'inline-block';
                submitText.textContent = 'Bayar Gaji';
            }, 15000);
        });
    }
});
</script>
@endpush
@endsection
