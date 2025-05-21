<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    use HasFactory;

    protected $table = 'pendapatan';

    protected $primaryKey = 'id_pendapatan';

    protected $fillable = [
        'id_pesanan',
        'id_barber', 
        'id_pelanggan', 
        'tanggal_bayar', 
        'nominal_bayar'
    ];

    public function pesanan()
    {
    return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    public function tukang_cukur()
    {
        return $this->belongsTo(TukangCukur::class, 'id_barber');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

}
