@extends('layouts.app')

@section('title', 'Laporan Penggajian | Panel Admin')

@section('content')
                <div class="card">
                    <div class="card-header">
                        Dashboard
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Selamat datang, {{ Auth::user()->nama }}!</h5>
                        <p class="card-text">Anda telah masuk sebagai Admin.</p>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">Pelanggan</div>
                            <div class="card-body">
                                <h5 class="card-title">Total Pelanggan</h5>
                                <p class="card-text display-4">{{ App\Models\Pelanggan::count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">Tukang Cukur</div>
                            <div class="card-body">
                                <h5 class="card-title">Total Tukang Cukur</h5>
                                <p class="card-text display-4">{{ App\Models\TukangCukur::count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-header">Data Pesanan</div>
                            <div class="card-body">
                                <h5 class="card-title">Total Data Pesanan</h5>
                                <p class="card-text display-4">{{ App\Models\Pesanan::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
@endsection
