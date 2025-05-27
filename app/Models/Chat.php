<?php
// File: app/Models/Chat.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'barber_id',
        'pelanggan_id',
        'last_message',
        'last_message_at',
        'barber_unread_count',
        'pelanggan_unread_count',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Pesanan::class, 'booking_id');
    }

    public function barber()
    {
        return $this->belongsTo(TukangCukur::class, 'barber_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }
}
