<?php

namespace App\Notifications;

use App\Comment;
use App\Notification as AppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommentPosted extends Notification
{
    use Queueable;

    protected $comment;
    protected $metadata;
    protected $resourceMetadata;

    /**
     * Create a new notification instance.
     * @param App\Comment $comment
     * @param array $metadata
     * @param array $resourceMetadata
     * @return void
     */
    public function __construct(Comment $comment, array $metadata = [], array $resourceMetadata = []) {
        $metadata = array_merge($metadata, ['persistence' => 'none']);
        $this->comment = $comment;
        $this->metadata = $metadata;
        $this->resourceMetadata = $resourceMetadata;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable) : array {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable) : array {
        $res = [
            'id' => $this->comment->commentable_id,
            'type' => $this->comment->commentable_type,
        ];

        if(!empty($this->resourceMetadata)) {
            $res['meta'] = $this->resourceMetadata;
        }

        return [
            'content' => $this->comment->content,
            'comment' => $this->comment->id,
            'in_reply' => isset($this->comment->reply_to),
            'user_id' => $this->comment->user_id,
            'metadata' => $this->metadata,
            'resource' => $res,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  object  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage {
        return new BroadcastMessage([
            'content' => $notifiable->notifications->first(),
        ]);
    }
}
