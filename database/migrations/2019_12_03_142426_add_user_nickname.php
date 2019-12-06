<?php

use App\User;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddUserNickname extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::all();
        Schema::table('users', function (Blueprint $table) {
            $table->text('nickname')->nullable();
        });

        foreach($users as $u) {
            $u->nickname = Str::lower(Str::before($u->name, ' '));
            $u->save();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->text('nickname')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nickname');
        });
    }
}
