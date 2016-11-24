<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIconOptionsFinds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finds', function (Blueprint $table) {
            $table->text('icon')->nullable();
            $table->text('color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finds', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->dropColumn('color');
        });
    }
}
