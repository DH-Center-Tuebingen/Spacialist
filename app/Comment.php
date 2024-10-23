<?php

namespace App;

use App\Traits\SoftDeletesWithTrashed;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use SoftDeletesWithTrashed;
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'user_id',
        'rules',
        'reply_to',
        'content',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $with = [
        'author'
    ];

    const keys = [
        'content' => 'nullable|string',
        'metadata' => 'nullable|array',
        'reply_to' => 'nullable|integer|exists:comments,id'
    ];

    const postKeys = [
        'content' => 'nullable|string',
        'metadata' => 'nullable|array',
        'reply_to' => 'nullable|integer|exists:comments,id',
        'resource_id' => 'required|integer',
        'resource_type' => 'required|string',
    ];

    const patchKeys = [
        'content' => 'required|nullable|string'
    ];

    /**
     * Get the owning commentable model.
     */
    public function commentable() {
        return $this->morphTo();
    }

    public function replies() {
        return $this->hasMany('App\Comment', 'reply_to');
    }

    public function author() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    
    public static function boot(){
        parent::boot();
        static::deleting(function($comment){
            $comment->replies()->delete();
        });
    }
}
