{{-- File: resources/views/pembayaran/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Pembayaran Pesanan | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Pembayaran</h5>
        <a href="{{ route('pesanan.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
        </a>
    </div>

    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Informasi Pesanan</h6>
                <table class="table table-bordered">
                    <tr>
                        <th>ID Transaksi</th>
                        <td>{{ $pesanan->id_transaksi }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Pesanan</th>
                        <td>{{ $pesanan->tgl_pesanan }}</td>
                    </tr>
                    <tr>
                        <th>Tukang Cukur</th>
                        <td>{{ $pesanan->tukangCukur->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Pelanggan</th>
                        <td>{{ $pesanan->pelanggan->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $pesanan->email }}</td>
                    </tr>
                    <tr>
                        <th>No. Telepon</th>
                        <td>{{ $pesanan->telepon }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $pesanan->alamat_lengkap }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h6>Rincian Pembayaran</h6>
                <table class="table table-bordered">
                    <tr>
                        <th>Biaya Layanan</th>
                        <td>Rp {{ number_format($pesanan->nominal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Ongkos Kirim</th>
                        <td>Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-primary">
                        <th>Total Pembayaran</th>
                        <td>Rp {{ number_format($pesanan->getTotalAmount(), 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td>
                            @if($pesanan->status_pembayaran == 'pending')
                                <span class="badge bg-warning">Menunggu Pembayaran</span>
                            @elseif($pesanan->status_pembayaran == 'paid')
                                <span class="badge bg-success">Pembayaran Selesai</span>
                                @if($pesanan->paid_at)
                                    <br><small>Dibayar pada: {{ \Carbon\Carbon::parse($pesanan->paid_at)->format('d M Y H:i') }}</small>
                                @endif
                            @elseif($pesanan->status_pembayaran == 'failed')
                                <span class="badge bg-danger">Pembayaran Gagal</span>
                            @elseif($pesanan->status_pembayaran == 'expired')
                                <span class="badge bg-secondary">Pembayaran Kedaluwarsa</span>
                            @endif
                        </td>
                    </tr>
                    @if($pesanan->payment_method)
                    <tr>
                        <th>Metode Pembayaran</th>
                        <td>{{ ucfirst($pesanan->payment_method) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-md-8 text-center">
                @if($pesanan->status_pembayaran == 'pending')
                    <div class="alert alert-info">
                        <h5>Lanjutkan Pembayaran</h5>
                        <p>Silakan lanjutkan pembayaran untuk menyelesaikan pesanan Anda.</p>
                    </div>

                    {{-- Midtrans Payment Button --}}
                    <form action="{{ route('pembayaran.process', $pesanan->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card"></i> Bayar Sekarang
                        </button>
                    </form>

                    {{-- Manual Payment Option --}}
                    <div class="mt-5">
                        <h6>Atau Transfer Manual</h6>
                        <div class="card mt-2">
                            <div class="card-body">
                                <p><strong>Bank BCA</strong></p>
                                <p>No. Rekening: 1234567890</p>
                                <p>Atas Nama: PT Barber Shop</p>
                                <p>Jumlah: Rp {{ number_format($pesanan->getTotalAmount(), 0, ',', '.') }}</p>
                                <hr>
                                <p class="text-muted">Setelah melakukan transfer, harap konfirmasi melalui WhatsApp ke nomor 081234567890 dengan menyertakan bukti transfer dan ID Transaksi: <strong>{{ $pesanan->id_transaksi }}</strong></p>
                            </div>
                        </div>
                    </div>
                @elseif($pesanan->status_pembayaran == 'paid')
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Pembayaran Berhasil</h5>
                        <p>Terima kasih! Pesanan Anda sudah berhasil dibayar.</p>
                    </div>
                    <a href="{{ route('pesanan.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lihat Semua Pesanan
                    </a>
                @else
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Pembayaran Belum Selesai</h5>
                        <p>Silakan coba kembali melakukan pembayaran.</p>
                    </div>
                    <form action="{{ route('pembayaran.process', $pesanan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Coba Lagi
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(config('services.midtrans.snap_url') && $pesanan->status_pembayaran == 'pending')
<script src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    // Add Midtrans-specific JavaScript if needed
</script>
@endif
@endsection
