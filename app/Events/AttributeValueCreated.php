<?php

namespace App\Events;

use App\AttributeValue;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttributeValueCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $value;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public AttributeValue $attributeValue,
        public User $user,
    )
    {
        $this->attributeValue = $attributeValue;
        $this->user = $user;
        $this->value = $attributeValue->getValue();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.entity.' . $this->attributeValue->entity->id),
        ];
    }
}
