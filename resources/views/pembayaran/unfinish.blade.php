@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-warning">
        <h4>Pembayaran Belum Selesai</h4>
        <p>Pembayaran untuk pesanan <strong>#{{ $pesanan->id }}</strong> belum selesai. Silakan coba lagi.</p>
        <a href="{{ route('pesanan.show', $pesanan->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Pesanan
        </a>
    </div>
</div>
@endsection
