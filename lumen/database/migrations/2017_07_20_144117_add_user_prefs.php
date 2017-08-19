<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserPrefs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->text('label');
            $table->jsonb('default_value');
            $table->boolean('allow_override')->nullable()->default(false);
            $table->timestamps();
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->integer('pref_id');
            $table->integer('user_id');
            $table->jsonb('value');
            $table->timestamps();

            $table->foreign('pref_id')->references('id')->on('preferences')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('preferences');
    }
}
