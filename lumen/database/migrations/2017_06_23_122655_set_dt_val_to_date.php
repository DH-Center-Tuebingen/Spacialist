<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AttributeValue;
use App\Attribute;

class SetDtValToDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $storedValues = [];
        $types = Attribute::where('datatype', '=', 'date')->get();
        foreach($types as $t) {
            $values = AttributeValue::where('attribute_id', '=', $t->id)->get();
            foreach($values as $v) {
                $storedValues[] = ['id' => $v->id, 'value' => $v->str_val];
            }
        }
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('dt_val');
        });
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->date('dt_val')->nullable();
        });
        foreach($storedValues as $sv) {
            $val = str_replace('"', '', $sv['value']);
            $date = date_format(new DateTime($val), 'Y-m-d');
            AttributeValue::where('id', '=', $sv['id'])->update(['dt_val' => $date]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $storedValues = [];
        $values = AttributeValue::whereNotNull('dt_val')->get();
        foreach($values as $v) {
            $storedValues[] = ['id' => $v->id, 'value' => $v->dt_val];
        }
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('dt_val');
        });
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->timestampTz('dt_val')->nullable();
        });
        foreach($storedValues as $sv) {
            $date = new DateTime($sv['value']);
            AttributeValue::where('id', '=', $sv['id'])->update(['str_val' => $date]);
        }
    }
}
