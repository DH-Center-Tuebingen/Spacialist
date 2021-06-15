<?php

namespace App\Notifications;

use App\Entity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EntityUpdated extends Notification
{
    use Queueable;

    protected $entity;
    protected $metadata;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
        $this->metadata = [
            'persistence' => 'none',
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'resource' => [
                'id' => $this->entity->id,
            ],
            'user_id' => $this->entity->user_id,
            'metadata' => $this->metadata,
        ];
    }
}
