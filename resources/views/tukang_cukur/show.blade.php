@extends('layouts.app')

@section('title', 'Detail Tukang Cukur | Panel Admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Detail Tukang Cukur</h5>
        <div>
            <a href="{{ route('tukang_cukur.edit', $tukangCukur->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('tukang_cukur.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Nama:</th>
                        <td>{{ $tukangCukur->nama }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $tukangCukur->email }}</td>
                    </tr>
                    <tr>
                        <th>Telepon:</th>
                        <td>{{ $tukangCukur->telepon }}</td>
                    </tr>
                    <tr>
                        <th>Spesialisasi:</th>
                        <td>{{ $tukangCukur->spesialisasi ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tarif:</th>
                        <td>Rp {{ number_format($tukangCukur->harga, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Rekening:</th>
                        <td>{{ $tukangCukur->rekening_barber ?: '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Status:</th>
                        <td>
                            @if($tukangCukur->is_verified)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Terverifikasi
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock"></i> Menunggu Verifikasi
                                </span>
                            @endif
                        </td>
                    </tr>
                    @if($tukangCukur->verified_at)
                    <tr>
                        <th>Tanggal Verifikasi:</th>
                        <td>{{ $tukangCukur->verified_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Sertifikat:</th>
                        <td>
                            @if($tukangCukur->sertifikat)
                                <a href="{{ Storage::url($tukangCukur->sertifikat) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file"></i> Lihat Sertifikat
                                </a>
                            @else
                                <span class="text-muted">Tidak ada sertifikat</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat pada:</th>
                        <td>{{ $tukangCukur->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Terakhir diubah:</th>
                        <td>{{ $tukangCukur->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <hr>

        <h6 class="mb-3">Jadwal Kerja</h6>
        @if($jadwal->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedJadwal = $jadwal->groupBy(function($item) {
                                return $item->tanggal->format('Y-m-d');
                            });
                        @endphp
                        @foreach($groupedJadwal as $tanggal => $jadwalPerTanggal)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</td>
                                <td>
                                    @foreach($jadwalPerTanggal as $j)
                                        <span class="badge bg-primary">{{ $j->jam }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Belum ada jadwal yang ditentukan.</p>
        @endif

        <div class="mt-4">
            @if(!$tukangCukur->is_verified)
                <form action="{{ route('tukang_cukur.verify', $tukangCukur->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin memverifikasi tukang cukur ini?')">
                        <i class="fas fa-check"></i> Verifikasi Sekarang
                    </button>
                </form>
            @else
                <form action="{{ route('tukang_cukur.unverify', $tukangCukur->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin membatalkan verifikasi?')">
                        <i class="fas fa-times-circle"></i> Batalkan Verifikasi
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
