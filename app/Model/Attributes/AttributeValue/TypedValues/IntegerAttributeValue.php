<?php

namespace App\Model\Attributes\AttributeValue\TypedValues;

use App\User;
use App\Entity;
use App\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegerAttributeValue extends Model
{
    protected $table = 'attribute_values_integer';
    
    protected $fillable = [
        'entity_id',
        'attribute_id',
        'value',
        'certainty',
        'user_id',
    ];

    protected $casts = [
        'value' => 'integer',
        'certainty' => 'integer',
    ];

    /**
     * Get all array items that reference this value.
     */
    public function arrayItems(): MorphMany
    {
        return $this->morphMany('App\Model\Attributes\AttributeValue\ArrayItem', 'item');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the serialized value compatible with the old AttributeValue model.
     */
    public function getValue(): mixed
    {
        return \App\AttributeTypes\AttributeBase::serializeValue($this);
    }

    /**
     * Patch the model with new values.
     */
    public function patch(array $values): void
    {
        foreach($values as $k => $v) {
            $this->{$k} = $v;
        }
        $this->save();
    }

    /**
     * Create a new integer value.
     */
    public static function createValue(int $value): self
    {
        return static::create(['value' => $value]);
    }
}
