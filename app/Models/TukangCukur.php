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
        'nama_bank',
        'rekening_barber', // Kolom baru untuk nomor rekening barber
        'is_verified',
        'verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'harga' => 'decimal:2', // Perubahan dari persentase_komisi menjadi harga
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

     // Scope untuk mendapatkan hanya tukang cukur yang sudah diverifikasi
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Scope untuk mendapatkan tukang cukur yang belum diverifikasi
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    // Method untuk memverifikasi tukang cukur
    public function verify()
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now()
        ]);
    }

    // Method untuk membatalkan verifikasi
    public function unverify()
    {
        $this->update([
            'is_verified' => false,
            'verified_at' => null
        ]);
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalTukangCukur::class, 'tukang_cukur_id');
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_barber');
    }

    public function penggajian()
    {
        return $this->hasMany(Penggajian::class, 'id_barber', 'id');
    }
}
