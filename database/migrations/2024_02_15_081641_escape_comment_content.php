<?php

use App\Comment;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        activity()->disableLogging();
        $comments = Comment::all();
        foreach($comments as $comment) {
            $comment->content = htmlspecialchars($comment->content);
            $comment->saveQuietly();
        }
        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();
        $comments = Comment::all();
        foreach($comments as $comment) {
            $comment->content = htmlspecialchars_decode($comment->content);
            $comment->saveQuietly();
        }
        activity()->enableLogging();
    }
};
