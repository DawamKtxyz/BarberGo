@extends('layouts.app')

@section('title', 'Tambah Tukang Cukur | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Tukang Cukur</h5>
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

        <form action="{{ route('tukang_cukur.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="telepon" class="form-label">Telepon <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon') }}" required>
                @error('telepon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="spesialisasi" class="form-label">Spesialisasi</label>
                <textarea class="form-control @error('spesialisasi') is-invalid @enderror" id="spesialisasi" name="spesialisasi" rows="3">{{ old('spesialisasi') }}</textarea>
                @error('spesialisasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Ganti form input persentase_komisi -->
            <div class="mb-3">
                <label for="harga" class="form-label">Harga Jasa (Rp) <span class="text-danger">*</span></label>
                <input type="number" step="1000" min="0" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga" value="{{ old('harga', 0) }}" required>
                @error('harga')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Masukkan harga jasa potong rambut yang ditawarkan</small>
            </div>

            <!-- Input dropdown untuk nama bank -->
            <div class="mb-3">
                <label for="nama_bank" class="form-label">Nama Bank</label>
                <select class="form-select @error('nama_bank') is-invalid @enderror" id="nama_bank" name="nama_bank">
                    <option value="">-- Pilih Bank --</option>
                    <option value="BCA" {{ old('nama_bank') == 'BCA' ? 'selected' : '' }}>BCA</option>
                    <option value="BRI" {{ old('nama_bank') == 'BRI' ? 'selected' : '' }}>BRI</option>
                    <option value="BNI" {{ old('nama_bank') == 'BNI' ? 'selected' : '' }}>BNI</option>
                    <option value="Mandiri" {{ old('nama_bank') == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                    <option value="CIMB Niaga" {{ old('nama_bank') == 'CIMB Niaga' ? 'selected' : '' }}>CIMB Niaga</option>
                    <option value="BTN" {{ old('nama_bank') == 'BTN' ? 'selected' : '' }}>BTN</option>
                    <option value="Danamon" {{ old('nama_bank') == 'Danamon' ? 'selected' : '' }}>Danamon</option>
                    <option value="Permata" {{ old('nama_bank') == 'Permata' ? 'selected' : '' }}>Permata</option>
                    <option value="Maybank" {{ old('nama_bank') == 'Maybank' ? 'selected' : '' }}>Maybank</option>
                    <option value="OCBC NISP" {{ old('nama_bank') == 'OCBC NISP' ? 'selected' : '' }}>OCBC NISP</option>
                    <option value="BSI (Bank Syariah Indonesia)" {{ old('nama_bank') == 'BSI (Bank Syariah Indonesia)' ? 'selected' : '' }}>BSI (Bank Syariah Indonesia)</option>
                    <option value="Jenius" {{ old('nama_bank') == 'Jenius' ? 'selected' : '' }}>Jenius</option>
                    <option value="Digibank" {{ old('nama_bank') == 'Digibank' ? 'selected' : '' }}>Digibank</option>
                </select>
                @error('nama_bank')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Pilih nama bank untuk rekening barber</small>
            </div>

            <!-- Input baru untuk rekening barber -->
            <div class="mb-3">
                <label for="rekening_barber" class="form-label">Nomor Rekening Barber</label>
                <input type="text" class="form-control @error('rekening_barber') is-invalid @enderror" id="rekening_barber" name="rekening_barber" value="{{ old('rekening_barber') }}" placeholder="Masukkan nomor rekening">
                @error('rekening_barber')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Nomor rekening untuk transfer pembayaran komisi</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
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
                                            <input class="form-check-input" type="checkbox" id="jam{{ $idx }}_{{ str_replace(':', '', $jam) }}" name="jadwal[{{ $idx }}][jam][]" value="{{ $jam }}">
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
                <label for="sertifikat" class="form-label">Upload Sertifikat <span class="text-danger">*</span></label>
                <input type="file" class="form-control @error('sertifikat') is-invalid @enderror" id="sertifikat" name="sertifikat" required>
                @error('sertifikat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
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
