<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThesaurusValOnContextValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('context_values', function (Blueprint $table) {
            $table->text('thesaurus_val')->nullable();
            $table->dropColumn('th_val');
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
            $table->dropColumn('thesaurus_val');
            $table->text('th_val')->nullable();
        });
    }
}
