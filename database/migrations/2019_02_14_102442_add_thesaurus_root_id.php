<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThesaurusRootId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        activity()->disableLogging();

        Schema::table('attributes', function (Blueprint $table) {
            $table->integer('root_attribute_id')->nullable();
            
            $table->foreign('root_attribute_id')->references('id')->on('attributes')->onDelete('cascade');
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

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('root_attribute_id');
        });

        activity()->enableLogging();
    }
}
