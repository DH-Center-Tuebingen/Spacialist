<?php

namespace App;

use App\Geodata;
use App\AttributeTypes\AttributeBase;
use App\Exceptions\InvalidDataException;
use App\Exceptions\Status\UnprocessableContentException;
use App\Traits\CommentTrait;
use App\Traits\ModerationTrait;
use Clickbar\Magellan\Data\Geometries\Geometry;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use stdClass;

class AttributeValue extends Model implements Searchable
{
    use CommentTrait;
    use ModerationTrait;
    use LogsActivity;

    protected $table = 'attribute_values';
    public $searchableType = 'entity_attribute';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_id',
        'attribute_id',
        'entity_val',
        'dbl_val',
        'dt_val',
        'geography_val',
        'int_val',
        'json_val',
        'str_val',
        'thesaurus_val',
        'certainty',
        'user_id',
    ];

    // TODO always hide *_val in favor of (computed) value?
    protected $hidden = [
        'entity_val',
        'dbl_val',
        'dt_val',
        'geography_val',
        'int_val',
        'json_val',
        'str_val',
        'thesaurus_val'
    ];

    protected $appends = [
        'value'
    ];

    protected $casts = [
        'geography_val' => Geometry::class,
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

    public function getValue() {
        return AttributeBase::serializeValue($this);
    }

    public function getValueAttribute() {
        return $this->getValue();
    }

    public static function getValueFromKey($arr) {
        if(!isset($arr)) return null;

        if(isset($arr['str_val'])) {
            return $arr['str_val'];
        }
        if(isset($arr['int_val'])) {
            return $arr['int_val'];
        }
        if(isset($arr['dbl_val'])) {
            return $arr['dbl_val'];
        }
        if(isset($arr['entity_val'])) {
            return $arr['entity_val'];
        }
        if(isset($arr['thesaurus_val'])) {
            return $arr['thesaurus_val'];
        }
        if(isset($arr['json_val'])) {
            return json_decode($arr['json_val']);
        }
        if(isset($arr['dt_val'])) {
            return $arr['dt_val'];
        }
        if(isset($arr['geography_val'])) {
            return Geodata::arrayToWKT($arr['geography_val']);
        }
    }

    public static function getValueById($aid, $cid) {
        $av = self::where('attribute_id', $aid)
            ->where('entity_id', $cid)->first();
        if(!isset($av)) {
            return null;
        }
        return $av->getValue();
    }

    public function patch($values) {
        foreach($values as $k => $v) {
            $this->{$k} = $v;
        }
        $this->save();
    }
    
    public static function remove($entityId, $attributeId): ?AttributeValue {
        try{
            $attrval = AttributeValue::where([
                ['entity_id', '=', $entityId],
                ['attribute_id', '=', $attributeId],
            ])->firstOrFail();
            
             // If the user is moderated, he cannot delete the value directly
            if(auth()->user()->isModerated()) {
                $attrval->moderate('pending-delete', true);
            } else {
                $attrval->delete();
            }
            return $attrval;
        }catch(ModelNotFoundException $e){
            throw new Exception(__('This attribute value does either not exist or is in moderation state.'));
        }
    }
    
    public static function upsert($entityId, $attributeId, $value): ?AttributeValue{
        if(!isset($entityId) || !isset($attributeId)) {
            throw new \InvalidArgumentException('Entity ID and Attribute ID must be provided.');
        }
        
        // Check if entity_type does even have the attribute
        $entity = Entity::find($entityId);
        if(!$entity->entity_type->hasEntityAttribute($attributeId)) {
            throw new UnprocessableContentException(__('Attribute is not part of the entity type of this entity.'));
        }
        
        $alreadyModerated = AttributeValue::where('entity_id', $entityId)
                    ->where('attribute_id', $attributeId)
                    ->onlyModerated()
                    ->exists();

        $user = auth()->user();
        // Currently the logic is that a moderated state cannot be changed
        // by a moderated user.
        if($alreadyModerated && $user->isModerated()) {
            throw new Exception(__('This attribute value is in moderation state. A user with appropriate permissions has to accept or deny it first.'));
        }
        $attributeValue = AttributeValue::firstOrNew([
            'entity_id' => $entityId,
            'attribute_id' => $attributeId,
        ], [
            'certainty' => null
        ]);

        $attribute = Attribute::findOrFail($attributeId);
        $formKeyValue = AttributeValue::getFormattedKeyValue($attribute->datatype, $value);
   
        $attributeValue->entity_id = $entityId;
        $attributeValue->attribute_id = $attributeId;
        $attributeValue->{$formKeyValue->key} = $formKeyValue->val;
        $attributeValue->user_id = $user->id;
        $attributeValue->save();

        
        if($user->isModerated()) {
            $attributeValue = $attributeValue->moderate('pending', false, true);
            unset($attributeValue->comments_count);
        }
        
        return $attributeValue;
    }

    public static function getFormattedKeyValue($datatype, $rawValue) : stdClass {
        $class = AttributeBase::getMatchingClass($datatype);
        if($class == false) {
            throw new InvalidDataException("Attribute of type '$datatype' is not supported. Maybe the underlying plugin is disabled or missing! You may still save all other properties.");
        }
        $keyValue = new stdClass();
        $keyValue->key = $class::getField();
        $keyValue->val = $class::unserialize($rawValue);

        return $keyValue;
    }

    // Does not handle InvalidDataException and AmbiguousValueException in stringToValue method here!
    // Throws InvalidDataException
    // Throws AmbiguousValueException
    public function setValueFromRaw($strValue, $type = null, $save = false) {
        if(!isset($type)) {
            $type = Attribute::first($this->attribute_id)->datatype;
        }
        $col = self::getValueColumn($type);

        if(!isset($col)) return;

        $value = self::stringToValue($strValue, $type);
        $this->{$col} = $value;

        if($save) {
            $this->save();
        }

        return $value;
    }

    public static function getValueColumn($type) {
        return AttributeBase::getFieldFromType($type);
    }

    public static function generateObject($attributeValues) {
        $data = [];
        foreach($attributeValues as $attributeValue) {
            $value = $attributeValue->getValue();
            if($attributeValue->moderation_state == 'pending-delete') {
                $attributeValue->value = [];
                $attributeValue->original_value = $value;
            } else {
                $attributeValue->value = $value;
            }
            if(isset($data[$attributeValue->attribute_id])) {
                $oldAttr = $data[$attributeValue->attribute_id];
                // check if stored entry is moderated one
                // if so, add current value as original value
                // otherwise, set stored entry as original value
                if(isset($oldAttr->moderation_state)) {
                    $oldAttr->original_value = $value;
                    $attributeValue = $oldAttr;
                } else {
                    $attributeValue->original_value = $oldAttr->value;
                }
            }
            $data[$attributeValue->attribute_id] = $attributeValue;
        }

        return $data;
    }

    // Throws InvalidDataException
    // Throws AmbiguousValueException
    public static function stringToValue(string $strValue, string $type) {
        $strValue = trim($strValue);
        if($strValue === '') return null;

        $attributeClass = AttributeBase::getMatchingClass($type);
        return $attributeClass::fromImport($strValue);
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function entity() {
        return $this->belongsTo('App\Entity');
    }

    public function attribute() {
        return $this->belongsTo('App\Attribute');
    }

    public function entity_value() {
        return $this->belongsTo('App\Entity', 'entity_val');
    }

    public function concept() {
        return $this->belongsTo('App\ThConcept', 'thesaurus_val', 'concept_url');
    }
}
