<?php

use App\User;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddUserNickname extends Migration
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

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        activity()->disableLogging();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nickname');
        });

        activity()->enableLogging();
    }
}
