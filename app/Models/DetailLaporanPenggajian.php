<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailLaporanPenggajian extends Model
{
    use HasFactory;

    protected $table = 'detail_laporan_penggajian';

    protected $fillable = [
        'id_laporan',
        'id_pesanan',
        'id_pelanggan',
        'nama_pelanggan',
        'tanggal_pesanan',
        'nominal_bayar'
    ];

    protected $casts = [
        'tanggal_pesanan' => 'date',
        'nominal_bayar' => 'decimal:2'
    ];

    // Relasi ke laporan penggajian
    public function laporanPenggajian()
    {
        return $this->belongsTo(LaporanPenggajian::class, 'id_laporan', 'id_gaji');
    }

    // Relasi ke pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id');
    }

    // Relasi ke pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id');
    }
}
