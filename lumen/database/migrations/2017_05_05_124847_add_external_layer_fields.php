<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AvailableLayer;

class AddExternalLayerFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('available_layers', function (Blueprint $table) {
            $table->text('api_key')->nullable();
            $table->text('layer_type')->nullable();
            $table->integer('position')->nullable();
        });

        $blCnt = 1;
        $olCnt = 1;
        $layers = AvailableLayer::orderBy('id', 'asc')->get();
        foreach($layers as $l) {
            if($l->is_overlay) {
                $l->position = $olCnt;
                $olCnt++;
            } else {
                $l->position = $blCnt;
                $blCnt++;
            }
            $l->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('available_layers', function (Blueprint $table) {
            $table->dropColumn('api_key');
            $table->dropColumn('layer_type');
            $table->dropColumn('position');
        });
    }
}
