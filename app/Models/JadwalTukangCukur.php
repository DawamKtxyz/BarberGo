<?php
// app/Models/JadwalTukangCukur.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class JadwalTukangCukur extends Model
{
    use HasFactory, SoftDeletes;

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

    // Kolom yang akan di-cast sebagai Carbon instance
    protected $dates = ['deleted_at'];

    // Method untuk debugging - mendeteksi kapan data dihapus
    protected static function boot()
    {
        parent::boot();

        // Log ketika data akan dihapus (soft delete)
        static::deleting(function($model) {
            Log::info('=== JADWAL TUKANG CUKUR AKAN DIHAPUS ===');
            Log::info('ID: ' . $model->id);
            Log::info('Tanggal: ' . $model->tanggal);
            Log::info('Jam: ' . $model->jam);
            Log::info('Tukang Cukur ID: ' . $model->tukang_cukur_id);
            Log::info('Waktu Penghapusan: ' . now());

            // Stack trace untuk tahu dari mana fungsi delete dipanggil
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            Log::info('Stack trace (dari mana data dihapus):');
            foreach($backtrace as $index => $trace) {
                if(isset($trace['file']) && isset($trace['line'])) {
                    Log::info("#{$index} File: {$trace['file']} Line: {$trace['line']}");
                    if(isset($trace['class']) && isset($trace['function'])) {
                        Log::info("    Method: {$trace['class']}->{$trace['function']}()");
                    }
                }
            }
            Log::info('=== END DELETE LOG ===');
        });

        // Log ketika data benar-benar dihapus permanen (force delete)
        static::forceDeleting(function($model) {
            Log::warning('=== JADWAL TUKANG CUKUR FORCE DELETE (PERMANEN) ===');
            Log::warning('ID: ' . $model->id);
            Log::warning('Tanggal: ' . $model->tanggal);
            Log::warning('Data akan dihapus permanen dari database!');
            Log::warning('=== END FORCE DELETE LOG ===');
        });

        // Opsional: Log ketika data baru dibuat
        static::created(function($model) {
            Log::info('Jadwal tukang cukur baru dibuat - ID: ' . $model->id . ', Tanggal: ' . $model->tanggal . ', Jam: ' . $model->jam);
        });

        // Opsional: Log ketika data diupdate
        static::updated(function($model) {
            $dirty = $model->getDirty(); // Ambil field yang berubah
            Log::info('Jadwal tukang cukur diupdate - ID: ' . $model->id);
            Log::info('Field yang berubah: ' . json_encode($dirty));
        });

        // Opsional: Log ketika data di-restore dari soft delete
        static::restored(function($model) {
            Log::info('Jadwal tukang cukur di-restore - ID: ' . $model->id . ', Tanggal: ' . $model->tanggal);
        });
    }

    // Relationships
    public function tukangCukur()
    {
        return $this->belongsTo(TukangCukur::class, 'tukang_cukur_id');
    }

    public function pesanan()
    {
        return $this->hasOne(Pesanan::class, 'jadwal_id');
    }

    // Scope untuk query data hari ini saja (jika diperlukan)
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    // Scope untuk query data berdasarkan tanggal tertentu
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    // Scope untuk query data yang belum ada pesanan
    public function scopeAvailable($query)
    {
        return $query->doesntHave('pesanan');
    }
}
