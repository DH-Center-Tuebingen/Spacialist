<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Context;

class AddPositionContext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contexts', function (Blueprint $table) {
            $table->integer('rank')->nullable();
        });

        $counters = [];
        $contexts = Context::all();
        foreach($contexts as $context) {
            $rid;
            if($context->root_context_id !== null) {
                $rid = $context->root_context_id;
            } else {
                $rid = 'root';
            }
            if(!array_key_exists($rid, $counters)) {
                $counters[$rid] = 1;
            }
            $context->rank = $counters[$rid];
            $counters[$rid]++;
            $context->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contexts', function (Blueprint $table) {
            $table->dropColumn('rank');
        });
    }
}
