<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class TukangCukur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tukang_cukur';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'telepon',
        'spesialisasi',
        'harga', // Perubahan dari persentase_komisi menjadi harga
        'sertifikat',
        'profile_photo',
        'api_token',
        'persentase_komisi',

    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'harga' => 'decimal:2', // Perubahan dari persentase_komisi menjadi harga
    ];

    public function jadwal()
    {
        return $this->hasMany(JadwalTukangCukur::class, 'tukang_cukur_id');
    }
    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_barber');
    }
}
