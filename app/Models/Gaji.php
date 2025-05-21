<?php
// app/Models/Gaji.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    protected $table = 'gaji';

    protected $fillable = [
        'barber_id',
        'bulan',
        'jumlah_potong',
        'gaji_potong',
        'total_gaji',
    ];

    protected $casts = [
        'jumlah_potong' => 'integer',
        'gaji_potong' => 'integer',
        'total_gaji' => 'integer',
    ];

    // Relationships
    public function barber()
    {
        return $this->belongsTo(\App\Models\TukangCukur::class, 'barber_id');
    }
}
