<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    protected $table = 'sources';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'context_id',
        'attribute_id',
        'literature_id',
        'description',
        'lasteditor',
    ];

    const rules = [
        'bibliography_id' => 'required|integer|exists:literature,id',
        'description' => 'string|nullable'
    ];

    const patchRules = [
        'description' => 'string|nullable'
    ];

    public static function add($values, $user) {
        $reference = new self();
        foreach($values as $k => $v) {
            // TODO remove after table/column renaming
            if($k == 'bibliography_id') {
                $reference->literature_id = $v;
            } else {
                $reference->{$k} = $v;
            }
        }
        $reference->lasteditor = $user->name;
        $reference->save();
        $reference->bibliography; // Retrieve bibliography relation

        return $reference;
    }

    public function patch($values) {
        foreach($values as $k => $v) {
            $this->{$k} = $v;
        }
        $this->save();
    }

    public function context() {
        return $this->belongsTo('App\Context');
    }

    public function attribute() {
        return $this->belongsTo('App\Attribute');
    }

    public function bibliography() {
        return $this->belongsTo('App\Bibliography', 'literature_id');
    }
}
