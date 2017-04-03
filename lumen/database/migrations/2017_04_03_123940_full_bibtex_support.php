<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FullBibtexSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('literature', function (Blueprint $table) {
        	$table->text('annote')->nullable();
        	$table->text('chapter')->nullable();
        	$table->text('crossref')->nullable();
        	$table->text('edition')->nullable();
        	$table->text('institution')->nullable();
        	$table->text('key')->nullable();
        	$table->text('month')->nullable();
        	$table->text('note')->nullable();
        	$table->text('organization')->nullable();
        	$table->text('school')->nullable();
        	$table->text('series')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('literature', function (Blueprint $table) {
        	$table->dropColumn('annote');
        	$table->dropColumn('chapter');
        	$table->dropColumn('crossref');
        	$table->dropColumn('edition');
        	$table->dropColumn('institution');
        	$table->dropColumn('key');
        	$table->dropColumn('month');
        	$table->dropColumn('note');
        	$table->dropColumn('organization');
        	$table->dropColumn('school');
        	$table->dropColumn('series');
        });
    }
}
