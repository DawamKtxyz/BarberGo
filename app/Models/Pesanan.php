<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;
    protected $table = 'pesanan';
    protected $fillable = [
        'id_barber',
        'id_pelanggan',
        'jadwal_id',
        'tgl_pesanan',
        'nominal',
        'id_transaksi',
        'alamat_lengkap',
        'ongkos_kirim',
        'email',
        'telepon',
        'status_pembayaran',
        'payment_url',
        'payment_token',
        'payment_method',
        'paid_at'
    ];

    public function barber()
    {
        return $this->belongsTo(TukangCukur::class, 'id_barber');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalTukangCukur::class, 'jadwal_id');
    }

     // Tambahkan relasi ke Pendapatan jika diperlukan
    public function pendapatan()
    {
        return $this->hasOne(Pendapatan::class, 'id_pesanan');
    }

    public function penggajian()
    {
        return $this->hasMany(Penggajian::class, 'id_pesanan', 'id');
    }

     // Helper method to get total amount
    public function getTotalAmount()
    {
        return $this->nominal + $this->ongkos_kirim;
    }

}
