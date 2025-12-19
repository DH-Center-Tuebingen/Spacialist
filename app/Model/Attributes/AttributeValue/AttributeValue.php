<?php

/**
 * This is the base class for all attribute values.
 * It defines a common interface and shared functionality across different attribute value types.
 * Therefore ensuring compatibility and consistency when handling attribute values in the system.
 */

namespace App\Model\Attributes\AttributeValue;

use App\User;
use App\Entity;
use App\Attribute;
use App\Traits\CommentTrait;
use App\Traits\ModerationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

abstract class AttributeValue extends Model implements Searchable
{
    use HasFactory;
    use CommentTrait;
    use ModerationTrait;
    use LogsActivity;

    public $searchableType = 'entity_attribute';

    protected $fillable = [
        'attribute_id',
        'entity_id',
        'certainty',
        'user_id',
    ];

    protected $copyOn = [
        'entity_id',
        'attribute_id',
    ];

    const patchRules = [
        'certainty' => 'integer|between:0,100',
    ];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    public function getSearchResult(): SearchResult {
        return new SearchResult(
            $this,
            Entity::find($this->entity_id)->name,
        );
    }

    /**
     * Get the serialized value for this attribute value.
     * Must be implemented by concrete attribute value types.
     */
    abstract public function getValue();

    /**
     * Set the value from raw input.
     * Must be implemented by concrete attribute value types.
     */
    abstract public function setValueFromRaw($rawValue, $type = null, $save = false);

    public function patch($values) {
        foreach($values as $k => $v) {
            $this->{$k} = $v;
        }
        $this->save();
    }

    // Relationships
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function entity() {
        return $this->belongsTo(Entity::class);
    }

    public function attribute() {
        return $this->belongsTo(Attribute::class);
    }
}