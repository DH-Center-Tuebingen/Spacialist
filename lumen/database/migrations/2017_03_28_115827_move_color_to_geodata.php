<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveColorToGeodata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('geodata', function (Blueprint $table) {
            $table->text('color')->nullable();
        });


        // former icon specifications will now be translated into different colors
        App\Context::get()->each(function ($context) {
            $translate = [
                    'plus'          =>  '#3333FF',
                    'close'         =>  '#9933FF',
                    'circle'        =>  '#FF33FF',
                    'university'    =>  '#3399FF',
                    'circle-o'      =>  '#0000B8',
                    'dot-circle-o'  =>  '#FF3333',
                    'square'        =>  '#33FFFF',
                    'map-pin'       =>  '#FF3399',
                    'flag'          =>  '#B8B800',
                    'square-o'      =>  '#FF9933',
                    'star'          =>  '#33FF99',
                    'asterisk'      =>  '#33FF33',
                    'flag-o'        =>  '#99FF33',
                    'map-marker'    =>  '#FFFF33',
            ];
            if(isset($context->geodata_id) && isset($context->icon) && $context->icon != '') App\Geodata::where('id', $context->geodata_id)->update(['color' => $translate[$context->icon]]);
        });

        Schema::table('contexts', function(Blueprint $table) {
            $table->dropColumn('color');
            $table->dropColumn('icon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('contexts', function(Blueprint $table) {
            $table->text('icon')->nullable();
            $table->text('color')->nullable();
        });

        App\Geodata::get()->each(function ($geodata) {
            // former icon specifications will now be translated into different colors
            $translate = [
                    '#3333FF'   =>  'plus',
                    '#9933FF'   =>  'close',
                    '#FF33FF'   =>  'circle',
                    '#3399FF'   =>  'university',
                    '#0000B8'   =>  'circle-o',
                    '#FF3333'   =>  'dot-circle-o',
                    '#33FFFF'   =>  'square',
                    '#FF3399'   =>  'map-pin',
                    '#B8B800'   =>  'flag',
                    '#FF9933'   =>  'square-o',
                    '#33FF99'   =>  'star',
                    '#33FF33'   =>  'asterisk',
                    '#99FF33'   =>  'flag-o',
                    '#FFFF33'   =>  'map-marker',
            ];
            if(array_key_exists($geodata->color, $translate)) {
                App\Context::where('geodata_id', $geodata->id)->update(['icon' => $translate[$geodata->color]]);
            }
        });

        Schema::table('geodata', function(Blueprint $table) {
            $table->dropColumn('color');
        });
    }
}
