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
                        <th>Alamat:</th>
                        <td>{{ $tukangCukur->alamat ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Spesialisasi:</th>
                        <td>{{ $tukangCukur->spesialisasi ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tarif:</th>
                        <td>Rp {{ number_format($tukangCukur->harga, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Nama Bank:</th>
                        <td>{{ $tukangCukur->nama_bank ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nomor Rekening:</th>
                        <td>{{ $tukangCukur->rekening_barber ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
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
                                <a href="{{ asset($tukangCukur->sertifikat) }}" target="_blank" class="btn btn-sm btn-outline-primary">
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
                            <th>Hari</th>
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
                                <td>{{ \Carbon\Carbon::parse($tanggal)->locale('id')->dayName }}</td>
                                <td>
                                    @foreach($jadwalPerTanggal->sortBy('jam') as $j)
                                        <span class="badge bg-primary me-1">{{ $j->jam->format('H:i') }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Belum ada jadwal yang ditentukan.
            </div>
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

            <form action="{{ route('tukang_cukur.destroy', $tukangCukur->id) }}" method="POST" class="d-inline ms-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data tukang cukur ini? Data yang sudah dihapus tidak dapat dikembalikan.')">
                    <i class="fas fa-trash"></i> Hapus Data
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
