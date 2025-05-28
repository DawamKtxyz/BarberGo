@extends('layouts.app')

@section('title', 'Generate Penggajian | Panel Admin')

@push('styles')
<style>
    .generate-card {
        max-width: 600px;
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border-bottom: none;
        padding: 2rem 1.5rem 1.5rem;
    }

    .card-header h4 {
        margin: 0;
        font-weight: 600;
    }

    .card-header p {
        margin: 0.5rem 0 0;
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .card-body {
        padding: 2rem 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    .btn {
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }

    .alert {
        border: none;
        border-radius: 10px;
        padding: 1rem 1.5rem;
    }

    .alert-info {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        color: #0277bd;
    }

    .alert-info .fas {
        margin-right: 0.5rem;
    }

    .btn-loading {
        position: relative;
        pointer-events: none;
    }

    .btn-loading .spinner-border {
        width: 1rem;
        height: 1rem;
        margin-right: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .required {
        color: #dc3545;
    }

    .text-muted {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 2rem;
    }

    .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
    }

    .breadcrumb-item a:hover {
        color: #28a745;
    }

    .breadcrumb-item.active {
        color: #495057;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('penggajian.index') }}">
                    <i class="fas fa-money-bill-wave"></i> Penggajian
                </a>
            </li>
            <li class="breadcrumb-item active">Generate Gaji</li>
        </ol>
    </nav>

    <div class="generate-card">
        <div class="card">
            <div class="card-header text-center">
                <h4>
                    <i class="fas fa-cogs"></i>
                    Generate Data Penggajian
                </h4>
                <p>Buat data penggajian berdasarkan pesanan yang sudah selesai</p>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Terjadi Kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('penggajian.generate') }}" method="POST" id="generateForm">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user-tie"></i>
                            Pilih Barber (Opsional)
                        </label>
                        <select name="id_barber" class="form-select">
                            <option value="">-- Semua Barber --</option>
                            @foreach($barbers as $barber)
                                <option value="{{ $barber->id }}" {{ old('id_barber') == $barber->id ? 'selected' : '' }}>
                                    {{ $barber->nama }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Kosongkan untuk generate gaji semua barber sekaligus
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Tanggal Dari <span class="required">*</span>
                                </label>
                                <input type="date"
                                       name="tanggal_dari"
                                       class="form-control @error('tanggal_dari') is-invalid @enderror"
                                       value="{{ old('tanggal_dari') }}"
                                       required>
                                @error('tanggal_dari')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar-check"></i>
                                    Tanggal Sampai <span class="required">*</span>
                                </label>
                                <input type="date"
                                       name="tanggal_sampai"
                                       class="form-control @error('tanggal_sampai') is-invalid @enderror"
                                       value="{{ old('tanggal_sampai') }}"
                                       required>
                                @error('tanggal_sampai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Informasi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Generate akan membuat data penggajian dari pesanan yang sudah <strong>selesai</strong></li>
                            <li>Pesanan yang sudah ada di penggajian akan <strong>diabaikan</strong></li>
                            <li>Potongan default adalah <strong>5%</strong> dari total pembayaran</li>
                            <li>Status awal penggajian adalah <strong>"Belum Lunas"</strong></li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between pt-3">
                        <a href="{{ route('penggajian.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Kembali
                        </a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="loadingSpinner"></span>
                            <i class="fas fa-cogs" id="submitIcon"></i>
                            <span id="submitText">Generate Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateForm = document.getElementById('generateForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');
    const tanggalDari = document.querySelector('input[name="tanggal_dari"]');
    const tanggalSampai = document.querySelector('input[name="tanggal_sampai"]');

    // Date validation
    if (tanggalDari) {
        tanggalDari.addEventListener('change', function() {
            if (tanggalSampai) {
                tanggalSampai.min = this.value;
                if (tanggalSampai.value && tanggalSampai.value < this.value) {
                    tanggalSampai.value = this.value;
                }
            }
        });
    }

    if (tanggalSampai) {
        tanggalSampai.addEventListener('change', function() {
            if (tanggalDari && this.value < tanggalDari.value) {
                alert('Tanggal sampai tidak boleh lebih kecil dari tanggal dari');
                this.value = tanggalDari.value;
            }
        });
    }

    // Form submit handling
    if (generateForm) {
        generateForm.addEventListener('submit', function(e) {
            // Validation
            if (!tanggalDari.value || !tanggalSampai.value) {
                e.preventDefault();
                alert('Harap isi tanggal dari dan tanggal sampai');
                return;
            }

            if (tanggalSampai.value < tanggalDari.value) {
                e.preventDefault();
                alert('Tanggal sampai tidak boleh lebih kecil dari tanggal dari');
                return;
            }

            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            loadingSpinner.classList.remove('d-none');
            submitIcon.classList.add('d-none');
            submitText.textContent = 'Generating...';

            // Prevent double submission
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-loading');
                    loadingSpinner.classList.add('d-none');
                    submitIcon.classList.remove('d-none');
                    submitText.textContent = 'Generate Sekarang';
                }
            }, 30000); // 30 seconds timeout
        });
    }

    // Auto-fill today's date if empty
    const today = new Date().toISOString().split('T')[0];
    if (tanggalDari && !tanggalDari.value) {
        const oneWeekAgo = new Date();
        oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
        tanggalDari.value = oneWeekAgo.toISOString().split('T')[0];
    }
    if (tanggalSampai && !tanggalSampai.value) {
        tanggalSampai.value = today;
    }
});
</script>
@endpush
@endsection
