<?php

namespace App\Events;

use App\Comment;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Comment $comment,
        public User $user,
    )
    {
        $this->comment = $comment;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if(isset($this->comment->commentable_type)) {
            $room = sp_get_comment_room($this->comment->commentable_type);
        } else {
            $room = sp_get_comment_room(
                Comment::find($this->comment->reply_to)->commentable_type
            );
        }

        if(!isset($room)) return [];

        $cid = $this->comment->commentable_id;
        return [
            new PresenceChannel("room.$room.$cid"),
        ];
    }
}
