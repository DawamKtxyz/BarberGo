@extends('layouts.app')

@section('title', 'Edit Tukang Cukur | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Tukang Cukur</h5>
        <a href="{{ route('tukang_cukur.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
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

        <form action="{{ route('tukang_cukur.update', $tukangCukur->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $tukangCukur->nama) }}" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $tukangCukur->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="telepon" class="form-label">Telepon <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon', $tukangCukur->telepon) }}" required>
                @error('telepon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="spesialisasi" class="form-label">Spesialisasi</label>
                <textarea class="form-control @error('spesialisasi') is-invalid @enderror" id="spesialisasi" name="spesialisasi" rows="3">{{ old('spesialisasi', $tukangCukur->spesialisasi) }}</textarea>
                @error('spesialisasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="harga" class="form-label">Harga Jasa (Rp) <span class="text-danger">*</span></label>
                <input type="number" step="1000" min="0" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga" value="{{ old('harga', $tukangCukur->harga ?? 0) }}" required>
                @error('harga')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Masukkan harga jasa potong rambut yang ditawarkan</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
            </div>

            <div class="mb-4">
                <label class="form-label">Jadwal Kerja (Bulan {{ now()->format('F Y') }}) <small class="text-muted">(Opsional)</small></label>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Jadwal kerja bersifat opsional. Pilih jam kerja hanya untuk tanggal yang tersedia.
                </div>
                <div class="accordion" id="jadwalAccordion">
                    @foreach($tanggal as $idx => $tgl)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $idx }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $idx }}" aria-expanded="false" aria-controls="collapse{{ $idx }}">
                                {{ $tgl->format('d F Y') }} ({{ $tgl->format('l') }})
                            </button>
                        </h2>
                        <div id="collapse{{ $idx }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $idx }}" data-bs-parent="#jadwalAccordion">
                            <div class="accordion-body">
                                <input type="hidden" name="jadwal[{{ $idx }}][tanggal]" value="{{ $tgl->format('Y-m-d') }}">
                                <div class="row">
                                    @foreach($jamKerja as $jam)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            @php
                                                $tanggalFormatted = $tgl->format('Y-m-d');
                                                $isChecked = isset($jadwalExisting[$tanggalFormatted]) &&
                                                    $jadwalExisting[$tanggalFormatted]->contains(function($value) use ($jam) {
                                                        return $value->jam->format('H:i') == $jam;
                                                    });
                                            @endphp
                                            <input class="form-check-input" type="checkbox"
                                                id="jam{{ $idx }}_{{ str_replace(':', '', $jam) }}"
                                                name="jadwal[{{ $idx }}][jam][]"
                                                value="{{ $jam }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                            <label class="form-check-label" for="jam{{ $idx }}_{{ str_replace(':', '', $jam) }}">
                                                {{ $jam }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <label for="sertifikat" class="form-label">Upload Sertifikat <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                <input type="file" class="form-control @error('sertifikat') is-invalid @enderror" id="sertifikat" name="sertifikat">
                @if($tukangCukur->sertifikat)
                <div class="mt-2">
                    <small class="text-muted">Sertifikat saat ini: <a href="{{ asset($tukangCukur->sertifikat) }}" target="_blank">Lihat Sertifikat</a></small>
                </div>
                @endif
                @error('sertifikat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menambahkan "Select All" untuk setiap hari
        const accordionItems = document.querySelectorAll('.accordion-item');

        accordionItems.forEach((item, idx) => {
            const checkboxes = item.querySelectorAll('input[type="checkbox"]');
            const accordionBody = item.querySelector('.accordion-body .row');

            // Tambahkan tombol "Pilih Semua" dan "Batal Semua"
            const buttonRow = document.createElement('div');
            buttonRow.className = 'row mb-2';
            buttonRow.innerHTML = `
                <div class="col-12">
                    <button type="button" class="btn btn-sm btn-outline-primary me-2 select-all-btn">Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-danger deselect-all-btn">Batal Semua</button>
                </div>
            `;

            accordionBody.parentNode.insertBefore(buttonRow, accordionBody);

            // Tambahkan event listener untuk tombol "Pilih Semua"
            buttonRow.querySelector('.select-all-btn').addEventListener('click', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            });

            // Tambahkan event listener untuk tombol "Batal Semua"
            buttonRow.querySelector('.deselect-all-btn').addEventListener('click', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        });
    });
</script>
@endpush
