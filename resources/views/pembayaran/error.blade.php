@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-danger">
        <h4>Pembayaran Gagal</h4>
        <p>{{ $message }}</p>
        <a href="{{ route('pesanan.show', $pesanan->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Pesanan
        </a>
    </div>
</div>
@endsection
