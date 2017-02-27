<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTopConceptToThConcept extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('th_concept', 'is_top_concept')) {
            Schema::table('th_concept', function (Blueprint $table) {
                $table->boolean('is_top_concept')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('th_concept', function (Blueprint $table) {
            $table->dropColumn('is_top_concept');
        });
    }
}
