@extends('layouts.app')

@section('title', 'Detail Tukang Cukur | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Tukang Cukur</h5>
        <div>
            <a href="{{ route('tukang_cukur.edit', $tukangCukur->id) }}" class="btn btn-primary btn-sm me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('tukang_cukur.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="fw-bold">Informasi Tukang Cukur</h6>
                <table class="table table-borderless">
                    <tr>
                        <td width="150">Nama</td>
                        <td>: {{ $tukangCukur->nama }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>: {{ $tukangCukur->email }}</td>
                    </tr>
                    <tr>
                        <td>Telepon</td>
                        <td>: {{ $tukangCukur->telepon }}</td>
                    </tr>
                    <tr>
                        <td>Spesialisasi</td>
                        <td>: {{ $tukangCukur->spesialisasi ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td>Persentase Komisi</td>
                        <td>: {{ $tukangCukur->persentase_komisi }}%</td>
                    </tr>
                    <tr>
                        <td>Sertifikat</td>
                        <td>:
                            @if($tukangCukur->sertifikat)
                                <a href="{{ asset($tukangCukur->sertifikat) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-file-download"></i> Lihat Sertifikat
                                </a>
                            @else
                                <span class="text-muted">Belum diunggah</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h6 class="fw-bold">Jadwal Kerja (Bulan {{ now()->format('F Y') }})</h6>

                @if($jadwal->isEmpty())
                    <div class="alert alert-info">
                        Belum ada jadwal kerja yang ditentukan.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Hari</th>
                                    <th>Jam Kerja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedJadwal = $jadwal->groupBy(function($item) {
                                        return $item->tanggal->format('Y-m-d');
                                    });
                                @endphp

                                @foreach($groupedJadwal as $tanggal => $jadwalHarian)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($tanggal)->format('l') }}</td>
                                        <td>
                                            @foreach($jadwalHarian as $slot)
                                                <span class="badge bg-primary me-1">{{ $slot->jam->format('H:i') }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
