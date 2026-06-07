<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sender;
    public $receiver;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->sender = $message->sender;
        $this->receiver = $message->receiver;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->receiver->id),
            new Channel('chat.' . $this->sender->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid' => $this->message->uuid,
            'sent_id' => $this->message->sent_id,
            'receive_id' => $this->message->receive_id,
            'message' => $this->message->message,
            'status_seen' => $this->message->status_seen,
            'seen_at' => $this->message->seen_at,
            'created_by' => $this->message->created_by,
            'created_at' => $this->message->created_at,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'nomor_induk' => $this->sender->nomor_induk,
                'role' => $this->sender->role,
            ],
            'receiver' => [
                'id' => $this->receiver->id,
                'name' => $this->receiver->name,
                'nomor_induk' => $this->receiver->nomor_induk,
                'role' => $this->receiver->role,
            ],
        ];
    }
}
