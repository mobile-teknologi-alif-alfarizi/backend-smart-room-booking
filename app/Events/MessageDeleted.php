<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $senderId;
    public $receiverId;

    public function __construct(string $messageId, int $senderId, int $receiverId)
    {
        $this->messageId = $messageId;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->senderId),
            new Channel('chat.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid' => $this->messageId,
        ];
    }
}
