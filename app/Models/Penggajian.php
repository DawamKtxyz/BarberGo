<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penggajian extends Model
{
    use HasFactory;

    protected $table = 'penggajian';
    protected $primaryKey = 'id_gaji';

    protected $fillable = [
        'id_pesanan',
        'id_barber',
        'nama_barber',
        'rekening_barber',
        'id_pelanggan',
        'nama_pelanggan',
        'tanggal_pesanan',
        'jadwal_id',        // TAMBAHAN
        'tanggal_jadwal',   // TAMBAHAN
        'jam_jadwal',       // TAMBAHAN
        'total_bayar',
        'potongan',
        'total_gaji',
        'status',
        'bukti_transfer'
    ];

    protected $casts = [
        'tanggal_pesanan' => 'datetime',
        'total_bayar' => 'decimal:2',
        'potongan' => 'decimal:2',
        'total_gaji' => 'decimal:2'
    ];

    // Relasi ke pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id');
    }

    // Relasi ke tukang cukur
    public function barber()
    {
        return $this->belongsTo(TukangCukur::class, 'id_barber', 'id');
    }

    // Relasi ke pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id');
    }

    // Mutator untuk menghitung potongan dan total gaji otomatis
    public function setTotalBayarAttribute($value)
    {
        $this->attributes['total_bayar'] = $value;
        $this->attributes['potongan'] = $value * 0.05; // 5%
        $this->attributes['total_gaji'] = $value - ($value * 0.05);
    }

        public function jadwal()
    {
        return $this->belongsTo(JadwalTukangCukur::class, 'jadwal_id');
    }

    // Accessor untuk format jam jadwal
    public function getJamJadwalFormatAttribute()
    {
        if ($this->jam_jadwal) {
            return date('H:i', strtotime($this->jam_jadwal));
        }
        return '-';
    }

    // Accessor untuk format tanggal jadwal
    public function getTanggalJadwalFormatAttribute()
    {
        if ($this->tanggal_jadwal) {
            return date('d/m/Y', strtotime($this->tanggal_jadwal));
        }
        return '-';
    }
}
