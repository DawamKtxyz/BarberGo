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

    // Tambahkan appends untuk memastikan accessor status_gaji selalu ada
    protected $appends = ['status_gaji'];

    protected $fillable = [
        'id_barber',
        'nama_barber',
        'jumlah_pesanan',
        'jumlah_pelanggan',
        'total_pendapatan',
        'potongan_komisi',
        'total_gaji',
        'periode_dari',
        'periode_sampai'
        // Removed 'status' field as it will be taken from penggajian table
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

    // Method untuk mendapatkan status dari tabel penggajian
    public function getStatusGajiAttribute()
    {
        // Ambil status dari tabel penggajian berdasarkan periode dan barber
        $statusPenggajian = Penggajian::whereHas('pesanan', function($query) {
            $query->where('id_barber', $this->id_barber)
                  ->whereBetween('tgl_pesanan', [$this->periode_dari, $this->periode_sampai]);
        })->pluck('status')->unique();

        // Jika ada status "Lunas" maka dianggap "Sudah Digaji"
        if ($statusPenggajian->contains('lunas')) {
            return 'Sudah Digaji';
        }

        // Default "Belum Digaji"
        return 'Belum Digaji';
    }

    // Scope untuk filter berdasarkan status
    public function scopeFilterByStatus($query, $status)
    {
        if ($status == 'Sudah Digaji') {
            return $query->whereHas('detailLaporan.pesanan.penggajian', function($q) {
                $q->where('status', 'lunas');
            });
        } elseif ($status == 'Belum Digaji') {
            return $query->whereHas('detailLaporan.pesanan.penggajian', function($q) {
                $q->where('status', 'belum lunas');
            })->orWhereDoesntHave('detailLaporan.pesanan.penggajian');
        }

        return $query;
    }
}
