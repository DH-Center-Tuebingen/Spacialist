<?php

namespace App\Traits;

use App\Comment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait CommentTrait
{
    public function initializeCommentTrait() {
        $this->withCount[] = 'comments';
    }

    public function addComment($data, $user = null) {
        if(!isset($user)) $user = auth()->user();
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->content = $data['content'] ?? '';
        $comment->metadata = $data['metadata'] ?? [];

        if(isset($data['reply_to'])) {
            $comment->reply_to = $data['reply_to'];
            $comment->save();
        } else {
            $this->comments()->save($comment);
        }

        $comment->load('author');
        return $comment;
    }

    public function removeComment($cid, $callback = null) {
        try {
            $comment = Comment::findOrFail($cid);
        } catch(ModelNotFoundException $e) {
            return false;
        }

        if(isset($callback)) {
            $callback($comment);
        }

        $comment->delete();
        return true;
    }

    public function comments() {
        return $this->morphMany('App\Comment', 'commentable');
    }
}
