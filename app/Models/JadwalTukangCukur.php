<?php
// app/Models/JadwalTukangCukur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalTukangCukur extends Model
{
    use HasFactory;

    protected $table = 'jadwal_tukang_cukur';

    protected $fillable = [
        'tukang_cukur_id',
        'tanggal',
        'jam',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam' => 'datetime:H:i',
    ];

    // Relationships
    public function tukangCukur()
    {
        return $this->belongsTo(TukangCukur::class, 'tukang_cukur_id');
    }

    public function pesanan()
    {
        return $this->hasOne(Pesanan::class, 'jadwal_id');
    }
}
