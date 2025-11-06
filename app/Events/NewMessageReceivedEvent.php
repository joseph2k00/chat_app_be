<?php

namespace App\Events;

use App\Models\ConversationMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageReceivedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public int $conversationId;
    public string $messageContent;
    public string $senderName;
    public string $conversationTitle;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $userId,
        int $conversationId,
        string $messageContent,
        string $senderName,
        string $conversationTitle
    ) {
        $this->userId = $userId;
        $this->conversationId = $conversationId;
        $this->messageContent = $messageContent;
        $this->senderName = $senderName;
        $this->conversationTitle = $conversationTitle;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('new.conversation.received.' . $this->userId),
        ];
    }
    
    public function broadcastAs()
    {
        return 'new.conversation.received';
    }
}
