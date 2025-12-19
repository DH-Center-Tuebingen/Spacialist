<?php

namespace App\Model\Attributes\AttributeValue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ArrayItem extends Model
{
    protected $fillable = [
        'array_attribute_value_id',
        'item_id',
        'item_type',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Get the parent array attribute value.
     */
    public function arrayAttributeValue(): BelongsTo
    {
        return $this->belongsTo(ArrayAttributeValue::class, 'array_attribute_value_id');
    }

    /**
     * Get the polymorphic item (integer, string, double, etc.).
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the actual value from the polymorphic item.
     */
    public function getValue()
    {
        return $this->item?->value;
    }
}
