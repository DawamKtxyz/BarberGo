<?php
// File: app/Models/Message.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'sender_type', // 'barber' or 'pelanggan'
        'sender_id',
        'message',
        'message_type', // 'text', 'image', 'file'
        'file_path',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        if ($this->sender_type === 'barber') {
            return $this->belongsTo(TukangCukur::class, 'sender_id');
        } else {
            return $this->belongsTo(Pelanggan::class, 'sender_id');
        }
    }
}
