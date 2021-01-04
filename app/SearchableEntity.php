<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class SearchableEntity extends Model
{
    use SearchableTrait;

    protected $table = 'entities';

    protected $searchable = [
        'columns' => [
            'entities.name' => 10,
        ],
        'joins' => [

        ],
    ];

    protected $appends = [
        'parentIds',
        'parentNames',
    ];

    private function parents()
    {
        $ancestors = collect([$this]);

        while ($ancestors->last() && $ancestors->last()->root_entity_id !== null) {
            $parent = $this->where('id', '=', $ancestors->last()->root_entity_id)->get();
            $ancestors = $ancestors->merge($parent);
        }
        return [
            'ids' => $ancestors->pluck('id')->all(),
            'names' => $ancestors->pluck('name')->all(),
        ];
    }

    public function getParentIdsAttribute()
    {
        return $this->parents()['ids'];
    }

    public function getParentNamesAttribute()
    {
        return $this->parents()['names'];
    }

    public function getAncestorsAttribute()
    {
        $parents = array_reverse($this->parents()['names']);
        array_pop($parents);
        return $parents;
    }
}
