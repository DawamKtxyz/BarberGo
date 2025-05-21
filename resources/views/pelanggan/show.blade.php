@extends('layouts.app')

@section('title', 'Tambah Laporan Penggajian | Panel Admin')

@section('content')
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Pelanggan</h5>
                        <div>
                            <a href="{{ route('pelanggan.edit', $pelanggan->id) }}" class="btn btn-warning btn-sm me-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 200px;">ID</th>
                                <td>{{ $pelanggan->id }}</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $pelanggan->nama }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $pelanggan->email }}</td>
                            </tr>
                            <tr>
                                <th>Telepon</th>
                                <td>{{ $pelanggan->telepon ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $pelanggan->alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td>{{ $pelanggan->tanggal_lahir ? $pelanggan->tanggal_lahir->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Dibuat</th>
                                <td>{{ $pelanggan->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Diperbarui</th>
                                <td>{{ $pelanggan->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
@endsection
