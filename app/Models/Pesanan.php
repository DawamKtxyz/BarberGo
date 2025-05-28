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

    public function scopePaid($query)
    {
        return $query->where('status_pembayaran', 'paid');
    }

    /**
     * Scope untuk pesanan pending
     */
    public function scopePending($query)
    {
        return $query->where('status_pembayaran', 'pending');
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
    }

    /**
     * Scope untuk filter berdasarkan barber
     */
    public function scopeByBarber($query, $barberId)
    {
        return $query->where('id_barber', $barberId);
    }

    /**
     * Accessor untuk status pembayaran dalam bahasa Indonesia
     */
    public function getStatusPembayaranTextAttribute()
    {
        $statuses = [
            'paid' => 'Sudah Dibayar',
            'pending' => 'Menunggu Pembayaran',
            'failed' => 'Pembayaran Gagal',
            'expired' => 'Pembayaran Kedaluwarsa'
        ];

        return $statuses[$this->status_pembayaran] ?? 'Status Tidak Diketahui';
    }

    /**
     * Accessor untuk format tanggal jadwal
     */
    public function getTanggalJadwalFormatAttribute()
    {
        if ($this->jadwal && $this->jadwal->tanggal) {
            return $this->jadwal->tanggal->format('d/m/Y');
        }
        return '-';
    }

    /**
     * Accessor untuk format jam jadwal
     */
    public function getJamJadwalFormatAttribute()
    {
        if ($this->jadwal && $this->jadwal->jam) {
            return $this->jadwal->jam->format('H:i');
        }
        return '-';
    }

    /**
     * Accessor untuk format tanggal dan jam jadwal lengkap
     */
    public function getJadwalLengkapFormatAttribute()
    {
        if ($this->jadwal && $this->jadwal->tanggal && $this->jadwal->jam) {
            $tanggal = $this->jadwal->tanggal->format('d/m/Y');
            $jam = $this->jadwal->jam->format('H:i');
            return $tanggal . ' ' . $jam;
        }
        return '-';
    }

    /**
     * Accessor untuk format tanggal booking (deprecated - gunakan getTanggalJadwalFormatAttribute)
     */
    public function getTanggalBookingFormatAttribute()
    {
        return $this->getTanggalJadwalFormatAttribute();
    }

    /**
     * Accessor untuk format jam booking (deprecated - gunakan getJamJadwalFormatAttribute)
     */
    public function getJamBookingFormatAttribute()
    {
        return $this->getJamJadwalFormatAttribute();
    }

    /**
     * Accessor untuk format total harga
     */
    public function getTotalHargaFormatAttribute()
    {
        return 'Rp ' . number_format($this->getTotalAmount(), 0, ',', '.');
    }

    /**
     * Check apakah pesanan sudah dibayar
     */
    public function isPaid()
    {
        return $this->status_pembayaran === 'paid';
    }

    /**
     * Check apakah pesanan masih pending
     */
    public function isPending()
    {
        return $this->status_pembayaran === 'pending';
    }

    /**
     * Check apakah pesanan sudah expire
     */
    public function isExpired()
    {
        return $this->status_pembayaran === 'expired';
    }

    /**
     * Check apakah pesanan gagal
     */
    public function isFailed()
    {
        return $this->status_pembayaran === 'failed';
    }
}
