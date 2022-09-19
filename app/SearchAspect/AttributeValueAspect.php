<?php

namespace App\SearchAspect;

use App\AttributeValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Searchable\SearchAspect;

class AttributeValueAspect extends SearchAspect {

    public static $searchType = 'entity_attribute';

    public function getResults(string $term): Collection {
        $ilikeTerm = "%$term%";
        return AttributeValue::where('str_val', 'ILIKE', $ilikeTerm)
            ->orWhereRaw('int_val::text ILIKE ?', [$ilikeTerm])
            ->orWhereRaw('dbl_val::text ILIKE ?', [$ilikeTerm])
            // ->orWhere('entity_val', '')
            ->orWhereHas('entity_value', function(Builder $query) use ($ilikeTerm) {
                $query->where('name', 'ILIKE', $ilikeTerm);
            })
            ->orWhereHas('concept', function(Builder $query) use ($ilikeTerm) {
                $query->whereHas('labels', function(Builder $labelQuery) use ($ilikeTerm) {
                    $labelQuery->where('label', 'ILIKE', $ilikeTerm);
                });
            })
            ->orWhereRaw('json_val::text ILIKE ?', [$ilikeTerm])
            ->orWhereRaw('dt_val::text ILIKE ?', [$ilikeTerm])
            ->orWhereRaw('ST_AsText(geography_val) ILIKE ?', [$ilikeTerm])
            ->distinct()
            ->get();
        // return Geodata::whereRaw("ST_AsText(geom) ILIKE ?", [$ilikeTerm])->get();


        // protected const searchCols = [
        //     'str_val' => 10,
        //     // 'int_val' => 10,
        //     // 'dbl_val' => 10,
        //     // 'entity_val' => 10,
        //     'thesaurus_val' => 10,
        //     // 'json_val' => 10,
        //     // 'dt_val' => 10,
        //     // 'geography_val' => 10,
        // ];
    }
}