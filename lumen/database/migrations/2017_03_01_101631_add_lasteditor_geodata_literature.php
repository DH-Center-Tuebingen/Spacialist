<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLasteditorGeodataLiterature extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('geodata', function (Blueprint $table) {
            $table->text('lasteditor')->default('');
        });
        Schema::table('literature', function (Blueprint $table) {
            $table->text('lasteditor')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('geodata', function (Blueprint $table) {
            $table->dropColumn('lasteditor');
        });
        Schema::table('literature', function (Blueprint $table) {
            $table->dropColumn('lasteditor');
        });
    }
}
