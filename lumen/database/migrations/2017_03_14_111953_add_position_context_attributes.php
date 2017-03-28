<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ContextAttribute;

class AddPositionContextAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('context_attributes', function (Blueprint $table) {
            $table->integer('position')->nullable();
        });

        $contextAttributes = ContextAttribute::orderBy('context_type_id', 'asc')->get();
        $lastCtid = -1;
        $counter = 1;
        foreach($contextAttributes as $ca) {
            if($ca->context_type_id != $lastCtid) {
                $counter = 1;
                $lastCtid = $ca->context_type_id;
            }
            $ca->position = $counter;
            $ca->save();
            $counter++;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('context_attributes', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
}
