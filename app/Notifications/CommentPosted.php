<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommentPosted extends Notification
{
    use Queueable;

    protected $comment;
    protected $metadata;
    protected $resourceMetadata;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comment, $metadata = [], $resourceMetadata = [])
    {
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
}
