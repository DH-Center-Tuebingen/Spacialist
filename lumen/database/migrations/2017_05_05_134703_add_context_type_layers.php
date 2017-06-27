<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ContextType;
use App\AvailableLayer;

class AddContextTypeLayers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $types = ContextType::all();
        $lastPos = AvailableLayer::where('is_overlay', '=', true)->max('position') + 1;
        foreach($types as $t) {
            $id = $t->id;
            $layer = new AvailableLayer();
            $layer->name = '';
            $layer->url = '';
            $layer->type = 'MultiPolygon';
            $layer->is_overlay = true;
            $layer->position = $lastPos++;
            $layer->context_type_id = $id;
            $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
            $layer->save();
        }
        $layer = new AvailableLayer();
        $layer->name = 'Unlinked';
        $layer->url = '';
        $layer->type = 'unlinked';
        $layer->is_overlay = true;
        $layer->position = $lastPos++;
        $layer->color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        $layer->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $ctLayers = AvailableLayer::where('context_type_id', '>', 0)->delete();
        AvailableLayer::where('type', '=', 'unlinked')->delete();
    }
}
