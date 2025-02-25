<?php

namespace App\Observers;

use App\Comment;
use App\Events\CommentAdded;
use App\Events\CommentDeleted;
use App\Events\CommentUpdated;

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
        $user = auth()->user();
        if($comment->wasRecentlyCreated) {
            broadcast(new CommentAdded($comment, $user))->toOthers();
        } else {
            broadcast(new CommentUpdated($comment, $user))->toOthers();
        }
    }

    /**
     * Handle the Comment "deleting" event.
     *
     * @param \App\Comment  $comment
     * @return void
     */
    public function deleting(Comment $comment) : void {
        broadcast(new CommentDeleted($comment, $user = auth()->user()))->toOthers();
    }
}
