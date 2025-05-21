@extends('layouts.app')

@section('title', 'Laporan Penggajian | Panel Admin')

@section('content')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Gaji Barber</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barber</th>
                                        <th>Bulan</th>
                                        <th>Jumlah Potong</th>
                                        <th>Gaji per Potong</th>
                                        <th>Total Gaji</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gaji as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->barber->name }}</td>
                                            <td>{{ $item->bulan }}</td>
                                            <td>{{ $item->jumlah_potong }}</td>
                                            <td>Rp {{ number_format($item->gaji_potong, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($item->total_gaji, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
@endsection
