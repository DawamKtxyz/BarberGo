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
        'id_pendapatan',
        'id_barber',
        'jumlah_potong',
        'potongan_komisi',
        'total_gaji',
    ];

    public function barber()
    {
        return $this->belongsTo(TukangCukur::class, 'barber_id');
    }

    public function pendapatan()
    {
        return $this->belongsTo(Pendapatan::class, 'id_pendapatan');
    }
}
