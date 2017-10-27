<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\AttributeValue;
use App\ThConcept;
use App\ThConceptLabel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConvertEpochValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $epochEntries = AttributeValue::join('attributes as a', 'attribute_id', '=', 'a.id')
            ->where('a.datatype', 'epoch')
            ->get();
        foreach($epochEntries as $ee) {
            $attrVal = [];
            $jsonVal = json_decode($ee->json_val);
            if(!isset($jsonVal)) continue;

            if(isset($jsonVal->epoch) && isset($jsonVal->epoch->narrower_id)){
                $nid = $jsonVal->epoch->narrower_id;
                try {
                    $concept = ThConcept::findOrFail($nid);
                    $jsonVal->epoch = [
                        'concept_url' => $concept->concept_url
                    ];
                } catch(ModelNotFoundException $e) {
                    continue;
                }
                $ee->json_val = json_encode($jsonVal);
                $ee->save();
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
        $epochEntries = AttributeValue::join('attributes as a', 'attribute_id', '=', 'a.id')
            ->where('a.datatype', 'epoch')
            ->get();
        foreach($epochEntries as $ee) {
            $attrVal = [];
            $jsonVal = json_decode($ee->json_val);
            if(!isset($jsonVal)) continue;

            if(isset($jsonVal->epoch)){
                $url = $jsonVal->epoch->concept_url;
                $concept = ThConcept::where('concept_url', $url)->first();
                if(!isset($concept)) continue;
                $label = ThConceptLabel::where('concept_id', $concept->id)->value('label');
                $jsonVal->epoch = [
                    'narr' => $label,
                    'narrower_id' => $concept->id
                ];
            }
            $ee->json_val = json_encode($jsonVal);
            $ee->save();
        }
    }
}
