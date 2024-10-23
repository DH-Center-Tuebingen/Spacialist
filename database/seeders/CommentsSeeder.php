<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentsSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $user = \App\User::orderBy('id')->first();
        DB::table('comments')->insert([
            [
                "commentable_id" => 1,
                "commentable_type" => "App\\Entity",
                "user_id" => 1,
                "reply_to" => NULL,
                'content' => '(1) This is a comment on an entity by user admin without any mentions.',
                'created_at' => '2017-12-20T09:47:35.000000Z',
                'updated_at' => '2017-12-20T09:47:35.000000Z',
            ],[
                "commentable_id" => 1,
                "commentable_type" => "App\\Entity",
                "user_id" => 2,
                "reply_to" => 1,
                'content' => '(2) This is a reply from John Doe (2) to the previous comment (1) of the @admin with a mention.',
                'created_at' => '2017-12-20T09:50:35.000000Z',
                'updated_at' => '2017-12-20T09:50:35.000000Z',
            ],[
                "commentable_id" => 1,
                "commentable_type" => "App\\Entity",
                "user_id" => 2,
                "reply_to" => NULL,
                'content' => '(3) This is a regular post of John Doe (2) on the same entity.',
                'created_at' => '2017-12-20T09:53:35.000000Z',
                'updated_at' => '2017-12-20T09:53:35.000000Z',
            ],[
                "comentable_id" => 69,
                "commentable_type" => "attribute_values",
                "user_id" => 2,
                "reply_to" => NULL,
                'content' => '(4) This is John Doe on attribute value (1)',
                'created_at' => '2017-12-20T09:53:35.000000Z',
                'updated_at' => '2017-12-20T09:53:35.000000Z',
            ],[
                "comentable_id" => 69,
                "commentable_type" => "attribute_values",
                "user_id" => 1,
                "reply_to" => 4,
                'content' => '(5) This is admin (1) replying to John Doe (2) on attribute value (1)',
                'created_at' => '2017-12-20T09:53:35.000000Z',
                'updated_at' => '2017-12-20T09:53:35.000000Z',
            ],[
                "comentable_id" => 69,
                "commentable_type" => "attribute_values",
                "user_id" => 1,
                "reply_to" => NULL,
                'content' => '(6) This is admin (1) on attribute value (1)',
                'created_at' => '2017-12-20T09:53:35.000000Z',
                'updated_at' => '2017-12-20T09:53:35.000000Z',
            ]
            
        ]);
    }
}