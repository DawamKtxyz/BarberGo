@extends('layouts.app')

@section('title', 'Tambah Pesanan | Panel Admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tambah Pesanan</h5>
            <a href="{{ route('pesanan.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Oops!</strong> Ada kesalahan saat input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pesanan.store') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="id_barber">Tukang Cukur:</label>
                    <select id="id_barber" name="id_barber" class="form-control @error('id_barber') is-invalid @enderror">
                        <option value="">-- Pilih Tukang Cukur --</option>
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

                <!-- Jadwal Tukang Cukur -->
                <div class="form-group mb-3" id="jadwal-container" style="display: none;">
                    <label for="jadwal_id">Jadwal:</label>
                    <select id="jadwal_id" name="jadwal_id" class="form-control @error('jadwal_id') is-invalid @enderror">
                        <option value="">-- Pilih Jadwal --</option>
                    </select>
                    @error('jadwal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="no-jadwal-message" class="alert alert-warning mt-2" style="display: none;">
                        Tidak ada jadwal tersedia untuk tukang cukur ini.
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="id_pelanggan">Pelanggan:</label>
                    <select id="id_pelanggan" name="id_pelanggan" class="form-control @error('id_pelanggan') is-invalid @enderror">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($pelanggans as $pelanggan)
                            <option value="{{ $pelanggan->id }}" {{ old('id_pelanggan') == $pelanggan->id ? 'selected' : '' }}>
                                {{ $pelanggan->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_pelanggan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kolom email dari pelanggan -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" readonly>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kolom telepon dari pelanggan -->
                <div class="mb-3">
                    <label for="telepon" class="form-label">No. Telepon</label>
                    <input type="text" id="telepon" name="telepon" class="form-control" value="{{ old('telepon') }}" readonly>
                    @error('telepon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kolom alamat lengkap baru -->
                <div class="mb-3">
                    <label for="alamat_lengkap" class="form-label">Alamat Lengkap</label>
                    <textarea id="alamat_lengkap" name="alamat_lengkap" class="form-control" required>{{ old('alamat_lengkap') }}</textarea>
                    @error('alamat_lengkap')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nominal dari harga tukang cukur -->
                <div class="mb-3">
                    <label for="nominal" class="form-label">Nominal</label>
                    <input type="number" id="nominal" name="nominal" class="form-control" value="{{ old('nominal') }}" readonly>
                    @error('nominal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Kolom ongkos kirim baru -->
                <div class="mb-3">
                    <label for="ongkos_kirim" class="form-label">Ongkos Kirim</label>
                    <input type="number" id="ongkos_kirim" name="ongkos_kirim" class="form-control" value="{{ old('ongkos_kirim', 10000) }}" readonly>
                    @error('ongkos_kirim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_transaksi" class="form-label">ID Transaksi (opsional)</label>
                    <input type="text" name="id_transaksi" class="form-control" value="{{ old('id_transaksi') }}">
                    @error('id_transaksi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('pesanan.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const barberSelect = document.getElementById('id_barber');
        const jadwalContainer = document.getElementById('jadwal-container');
        const jadwalSelect = document.getElementById('jadwal_id');
        const noJadwalMessage = document.getElementById('no-jadwal-message');
        const nominalInput = document.getElementById('nominal');
        const pelangganSelect = document.getElementById('id_pelanggan');
        const emailInput = document.getElementById('email');
        const teleponInput = document.getElementById('telepon');
        const alamatInput = document.getElementById('alamat_lengkap');

        // Function to format date and time nicely
        function formatDateTime(date, time) {
            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            // Format time (assuming time is in HH:MM:SS format)
            const timeParts = time.split(':');
            const formattedTime = `${timeParts[0]}:${timeParts[1]}`;

            return `${formattedDate}, ${formattedTime}`;
        }

        // Load barber details when barber is selected
        barberSelect.addEventListener('change', function() {
            const barberId = this.value;

            // Reset and hide the jadwal section if no barber selected
            if (!barberId) {
                jadwalContainer.style.display = 'none';
                nominalInput.value = '';
                return;
            }

            // Fetch barber details for the price
            fetch(`/pesanan/get-barber-details/${barberId}`)
                .then(response => response.json())
                .then(data => {
                    // Set the nominal field with the barber's price
                    nominalInput.value = data.harga;
                })
                .catch(error => {
                    console.error('Error fetching barber details:', error);
                });

            // Fetch schedules for the selected barber
            fetch(`/pesanan/get-jadwal/${barberId}`)
                .then(response => response.json())
                .then(data => {
                    // Clear previous options
                    jadwalSelect.innerHTML = '<option value="">-- Pilih Jadwal --</option>';

                    if (data.length > 0) {
                        // Add jadwal options
                        data.forEach(jadwal => {
                            const option = document.createElement('option');
                            option.value = jadwal.id;
                            option.textContent = formatDateTime(jadwal.tanggal, jadwal.jam);
                            jadwalSelect.appendChild(option);
                        });

                        // Show jadwal selector, hide no-jadwal message
                        jadwalContainer.style.display = 'block';
                        jadwalSelect.style.display = 'block';
                        noJadwalMessage.style.display = 'none';
                    } else {
                        // Show no-jadwal message, hide jadwal selector
                        jadwalContainer.style.display = 'block';
                        jadwalSelect.style.display = 'none';
                        noJadwalMessage.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching jadwal:', error);
                });
        });

        // Load customer details when customer is selected
        pelangganSelect.addEventListener('change', function() {
            const pelangganId = this.value;

            if (!pelangganId) {
                emailInput.value = '';
                teleponInput.value = '';
                alamatInput.value = '';
                return;
            }

            // Fetch customer details
            fetch(`/pesanan/get-pelanggan-details/${pelangganId}`)
                .then(response => response.json())
                .then(data => {
                    // Set the email and telepon fields with customer data
                    emailInput.value = data.email;
                    teleponInput.value = data.telepon;
                    alamatInput.value = data.alamat || '';
                })
                .catch(error => {
                    console.error('Error fetching customer details:', error);
                });
        });

        // If there's an old value for barber, trigger the change event to load jadwal
        if (barberSelect.value) {
            barberSelect.dispatchEvent(new Event('change'));
        }

        // If there's an old value for customer, trigger the change event to load customer details
        if (pelangganSelect.value) {
            pelangganSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
