<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\User;

class EmailToLowercase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        $users = User::withoutGlobalScope(new SoftDeletingScope())
            ->get();

        foreach($users as $user) {
            $user->email = Str::lower($user->email);
            $user->nickname = Str::lower($user->nickname);
            $user->save();
        }

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
