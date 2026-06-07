<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSeen implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $senderId;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->senderId = $message->sent_id;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->senderId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.seen';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid' => $this->message->uuid,
            'status_seen' => true,
            'seen_at' => $this->message->seen_at,
        ];
    }
}
