<?php
// File: app/Events/MessageSent.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $barberId;
    public $pelangganId;

    public function __construct($message, $barberId, $pelangganId)
    {
        $this->message = $message;
        $this->barberId = $barberId;
        $this->pelangganId = $pelangganId;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat.barber.' . $this->barberId),
            new PrivateChannel('chat.pelanggan.' . $this->pelangganId),
        ];
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
        ];
    }
}
