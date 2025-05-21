<?php
// app/Models/Pelanggan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelanggan extends Authenticatable
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'api_token',
        'telepon',
        'alamat',
        'tanggal_lahir',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'api_token',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'email_verified_at' => 'datetime',
    ];

    // Relationships
    public function pesanan()
    {
        return $this->hasMany(\App\Models\Pesanan::class, 'id_pelanggan');
    }
}
