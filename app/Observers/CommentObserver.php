<?php

namespace App\Observers;

use App\Comment;
use App\Events\CommentAdded;
use App\Events\CommentDeleted;
use App\Events\CommentUpdated;
use Illuminate\Broadcasting\BroadcastException;

class CommentObserver
{
    /**
     * Handle the Comment "saving" event.
     *
     * @param \App\Comment  $comment
     * @return void
     */
    public function saving(Comment $comment) : void {
    }

    /**
     * Handle the Comment "saved" event.
     *
     * @param \App\Comment  $comment
     * @return void
     *
     */
    public function saved(Comment $comment) : void {
        try {
            $user = auth()->user();
            if($comment->wasRecentlyCreated) {
                broadcast(new CommentAdded($comment, $user))->toOthers();
            } else {
                broadcast(new CommentUpdated($comment, $user))->toOthers();
            }
        } catch(BroadcastException $e) {
            info("BroadcastException while handling saved() event in CommentObserver");
        }
    }

    /**
     * Handle the Comment "deleting" event.
     *
     * @param \App\Comment  $comment
     * @return void
     */
    public function deleting(Comment $comment) : void {
        try {
            broadcast(new CommentDeleted($comment, $user = auth()->user()))->toOthers();
        } catch(BroadcastException $e) {
            info("BroadcastException while handling deleting() event in CommentObserver");
        }
    }
}
