@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-success">
        <h4>Pembayaran Berhasil</h4>
        <p>Terima kasih! Pembayaran untuk pesanan <strong>#{{ $pesanan->id }}</strong> telah diproses.</p>
        <a href="{{ route('pesanan.show', $pesanan->id) }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Pesanan
        </a>
    </div>
</div>
@endsection
