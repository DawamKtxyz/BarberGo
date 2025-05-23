<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    use HasFactory;

    protected $table = 'pendapatan';

    protected $primaryKey = 'id_pendapatan';
    public $incrementing = false; // ✅ Karena kita pakai custom ID
    protected $keyType = 'string'; // ✅ ID berbentuk string

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

        // ✅ Auto generate ID saat create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_pendapatan)) {
                $model->id_pendapatan = self::generateIdPendapatan();
            }
        });
    }

       // ✅ Function untuk generate ID otomatis
    private static function generateIdPendapatan()
    {
        $lastPendapatan = self::orderBy('id_pendapatan', 'desc')->first();

        if (!$lastPendapatan) {
            return 'PD1';
        }

        // Ambil angka dari ID terakhir (misal: PD5 -> 5)
        $lastNumber = (int) str_replace('PD', '', $lastPendapatan->id_pendapatan);
        $newNumber = $lastNumber + 1;

        return 'PD' . $newNumber;
    }

}
