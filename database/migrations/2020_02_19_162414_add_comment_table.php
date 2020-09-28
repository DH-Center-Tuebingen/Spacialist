<?php

use App\AttributeValue;
use App\Comment;
use App\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('commentable_id')->nullable();
            $table->text('commentable_type')->nullable();
            $table->unsignedInteger('reply_to')->nullable();
            $table->text('content')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reply_to')->references('id')->on('comments')->onDelete('cascade');
        });

        $values = AttributeValue::all();

        foreach($values as $v) {
            try {
                $user = User::where('name', $v->lasteditor)->firstOrFail();
            } catch(ModelNotFoundException $e) {
                // skip adding comment, if user can not be found
                continue;
            }
            $comment = new Comment();
            $comment->user_id = $user->id;
            $comment->commentable_id = $v->id;
            $comment->commentable_type = 'attribute_values';
            $comment->content = $v->certainty_description;
            $comment->metadata = [
                'certainty_from' => null,
                'certainty_to' => $v->certainty
            ];
            $comment->save();
        }

        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('certainty_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->text('certainty_description')->nullable();
        });

        // TODO restore certainty_descriptions
        $comments = Comment::where('commentable_type', 'attribute_value')
            ->orderBy('updated_at')
            ->get();

        foreach($comments as $c) {
            $user = User::find($c->user_id);
            $val = AttributeValue::find($c->commentable_id);
            $val->certainty_description = $c->content;
            $val->lasteditor = $user->name;
            $val->save();
        }

        Schema::dropIfExists('comments');
    }
}
