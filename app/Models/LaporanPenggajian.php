<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPenggajian extends Model
{
    use HasFactory;

    protected $table = 'laporan_penggajian';
    protected $primaryKey = 'id_gaji';
    public $incrementing = true;

    protected $fillable = [
        'id_barber',
        'nama_barber',
        'jumlah_pesanan',
        'jumlah_pelanggan',
        'total_pendapatan',
        'potongan_komisi',
        'total_gaji',
        'periode_dari',
        'periode_sampai',
        'status'
    ];

    protected $casts = [
        'total_pendapatan' => 'decimal:2',
        'potongan_komisi' => 'decimal:2',
        'total_gaji' => 'decimal:2',
        'periode_dari' => 'date',
        'periode_sampai' => 'date'
    ];

    // Relasi ke tukang cukur
    public function barber()
    {
        return $this->belongsTo(TukangCukur::class, 'id_barber', 'id');
    }

    // Relasi ke detail laporan (pesanan-pesanan yang masuk dalam laporan ini)
    public function detailLaporan()
    {
        return $this->hasMany(DetailLaporanPenggajian::class, 'id_laporan', 'id_gaji');
    }

    // Accessor untuk format periode
    public function getPeriodeFormatAttribute()
    {
        if ($this->periode_dari && $this->periode_sampai) {
            return date('d/m/Y', strtotime($this->periode_dari)) . ' - ' . date('d/m/Y', strtotime($this->periode_sampai));
        }
        return '-';
    }

    // Accessor untuk mendapatkan persentase komisi
    public function getPersentaseKomisiAttribute()
    {
        if ($this->jumlah_pesanan > 0) {
            return $this->jumlah_pesanan * 5; // 5% per pesanan
        }
        return 0;
    }

    // Accessor untuk menampilkan detail komisi
    public function getDetailKomisiAttribute()
    {
        $persentase = $this->getPersentaseKomisiAttribute();
        return "{$persentase}% ({$this->jumlah_pesanan} pesanan × 5%)";
    }
}
