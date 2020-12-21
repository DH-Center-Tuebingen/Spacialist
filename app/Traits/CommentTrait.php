<?php

namespace App\Traits;

use App\Comment;
use App\Notifications\CommentPosted;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait CommentTrait
{
    public function initializeCommentTrait() {
        $this->withCount[] = 'comments';
    }

    public function addComment($data, $user = null, $notify = true, $resourceMetadata = []) {
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

        if($notify) {
            $oldComments = Comment::where('commentable_id', $comment->commentable_id)
                ->where('commentable_type', $comment->commentable_type)
                ->whereHas('author', function(Builder $query) use($user) {
                    $query->where('id', '<>', $user->id);
                    $query->whereNull('deleted_at');
                })
                ->select('user_id')
                ->groupBy('user_id')
                ->get();
            
            foreach($oldComments as $c) {
                $notifUser = User::find($c->user_id);
    
                $notifUser->notify(new CommentPosted($comment, [], $resourceMetadata));
            }
        }

        $comment = Comment::find($comment->id);
        return $comment;
    }

    public function comments() {
        return $this->morphMany('App\Comment', 'commentable')->orderBy('id');
    }
}
