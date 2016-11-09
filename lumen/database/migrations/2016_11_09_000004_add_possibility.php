<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPossibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('context_values', function (Blueprint $table) {
            $table->integer('possibility')->default(100)->comment('possibility for this attribute, as integer 0-100%');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('context_values', function (Blueprint $table) {
            $table->dropColumn('possibility');
        });
    }
}
