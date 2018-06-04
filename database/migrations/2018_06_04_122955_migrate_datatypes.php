<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Attribute;
use App\AttributeValue;

class MigrateDatatypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // epoch entries sometimes had keys from other attributes.
        // Valid keys are only ['start', 'startLabel', 'end', 'endLabel', 'epoch']
        $allowed = ['start', 'startLabel', 'end', 'endLabel', 'epoch'];

        $epoch_aid = Attribute::where('datatype', 'epoch')->pluck('id')->toArray();
        $epochs = AttributeValue::whereIn('attribute_id', $epoch_aid)->get();

        foreach($epochs as $e) {
            $value = json_decode($e->json_val, TRUE);
            $value = array_intersect_key($value, array_flip($allowed));
            $value = json_encode($value);
            $e->json_val = $value;
            $e->save();
        }

        // list data format changes
        $list_aid = Attribute::where('datatype', 'list')->pluck('id')->toArray();
        foreach($list_aid as $aid) {
            $lists = AttributeValue::where('attribute_id', $aid)->get();
            $context_ids = $lists->pluck('context_id')->unique()->toArray();
            foreach($context_ids as $cid) {
                $list = $lists->where('context_id', $cid)->values();
                $entries = $list->map(function ($item, $key) {
                    return $item->getValue();
                });
                $tmp = $list[0];
                $av = new AttributeValue();
                $av->context_id = $cid;
                $av->attribute_id = $aid;
                $av->json_val = $entries;
                $av->created_at = $tmp->created_at;
                $av->updated_at = $tmp->updated_at;
                $av->possibility = $tmp->possibility;
                $av->lasteditor = $tmp->lasteditor;
                $av->possibility_description = $tmp->possibility_description;
                $av->save();
                AttributeValue::whereIn('id', $list->pluck('id'))->delete();
            }
        }

        // table data format changes
        $table_aid = Attribute::where('datatype', 'table')->pluck('id')->toArray();
        foreach($table_aid as $table_id) {
            $tables = AttributeValue::where('attribute_id', $table_id)->orderBy('id')->get();
            $context_ids = $tables->pluck('context_id')->unique()->toArray();
            foreach($context_ids as $cid) {
                $table = $tables->where('context_id', $cid)->sortBy('id')->values();
                $rows = $table->map(function ($item, $key) {
                    return $item->getValue();
                });
                $newRows = [];
                foreach($rows as $row) {
                    $newRow = [];
                    foreach($row as $column) {
                        if(isset($column->attribute_id) && isset($column->value)) {
                            $aid = $column->attribute_id;
                            $value = $column->value;
                            $newRow[$aid] = $value;
                        }
                    }
                    array_push($newRows, $newRow);
                }
                $tmp = $table[0];
                $av = new AttributeValue();
                $av->context_id = $cid;
                $av->attribute_id = $table_id;
                $av->json_val = json_encode($newRows);
                $av->created_at = $tmp->created_at;
                $av->updated_at = $tmp->updated_at;
                $av->possibility = $tmp->possibility;
                $av->lasteditor = $tmp->lasteditor;
                $av->possibility_description = $tmp->possibility_description;
                $av->save();
                foreach($table as $row) {
                    $row->delete();
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // revert list data format changes
        $list_aid = Attribute::where('datatype', 'list')->pluck('id')->toArray();
        foreach($list_aid as $aid) {
            $lists = AttributeValue::where('attribute_id', $aid)->get();
            foreach($lists as $list) {
                $entries = $list->getValue();
                foreach($entries as $e) {
                    $av = new AttributeValue();
                    $av->context_id = $list->context_id;
                    $av->attribute_id = $list->attribute_id;
                    $av->created_at = $list->created_at;
                    $av->updated_at = $list->updated_at;
                    $av->possibility = $list->possibility;
                    $av->lasteditor = $list->lasteditor;
                    $av->possibility_description = $list->possibility_description;
                    $av->str_val = $e;
                    $av->save();
                }
                $list->delete();
            }
        }

        // revert table data format changes
        $table_aid = Attribute::where('datatype', 'table')->pluck('id')->toArray();
        foreach($table_aid as $table_id) {
            $tables = AttributeValue::where('attribute_id', $table_id)->get();
            foreach($tables as $table) {
                $rows = $table->getValue();
                foreach($rows as $r) {
                    $av = new AttributeValue();
                    $av->context_id = $table->context_id;
                    $av->attribute_id = $table->attribute_id;
                    $av->created_at = $table->created_at;
                    $av->updated_at = $table->updated_at;
                    $av->possibility = $table->possibility;
                    $av->lasteditor = $table->lasteditor;
                    $av->possibility_description = $table->possibility_description;
                    $value = [];
                    $i = 0;
                    foreach($r as $key => $column) {
                        $value[$i] = [
                            'attribute_id' => $key,
                            'datatype' => Attribute::find($key)->datatype,
                            'value' => $column
                        ];
                        $i++;
                    }
                    $av->json_val = json_encode($value, JSON_FORCE_OBJECT);
                    $av->save();
                }
                $table->delete();
            }
        }
    }
}
