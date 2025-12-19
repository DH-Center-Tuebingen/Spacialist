<?php

namespace App;

use App\Geodata;
use App\AttributeTypes\AttributeBase;
use App\AttributeTypes\SuperiorAttributeBase;
use App\Exceptions\InvalidDataException;
use Illuminate\Database\Eloquent\Model;
use Clickbar\Magellan\Data\Geometries\Geometry;
use App\Traits\CommentTrait;
use App\Traits\ModerationTrait;
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

    // [VR] Temporary solution to allow patching entities/attribute values outside of controller
    // But this is also already fixed (in a different way) on pull/585 (0.11.2-fix-attribute-value-list-error)
    public static function handlePatch(int $entity_id, int $attribute_id, mixed $value, string $operation, User $user, array &$added = [], array &$deleted = []) {
        $error = null;
        $code = 400;
        
        // Get attribute to determine if it's a superior type
        $attr = Attribute::find($attribute_id);
        if(!$attr) {
            return [
                'message' => __('This attribute does not exist'),
                'code' => 404,
            ];
        }
        
        $attributeClass = AttributeBase::getMatchingClass($attr->datatype);
        $isSuperiorType = is_a($attributeClass, SuperiorAttributeBase::class);
        $modelClass = $isSuperiorType ? $attributeClass::getModelClass() : AttributeValue::class;
        
        switch($operation) {
            case 'remove':
                $attrval = $modelClass::where([
                    ['entity_id', '=', $entity_id],
                    ['attribute_id', '=', $attribute_id],
                ])->first();
                if(!isset($attrval)) {
                    $error = __('This attribute value does either not exist or is in moderation state.');
                    break;
                }
                if($user->isModerated()) {
                    $attrval->moderate('pending-delete', true);
                } else {
                    $deleted[$attribute_id] = $attrval;
                    $attrval->delete();
                }
                break;

            /**
             * In the case when a user created the attribute, while another was visiting the
             * page and sends an 'add' operation, and the other user also sends their changes,
             * the application would have thrown an error, that the attribute was already created.
             *
             * That's why we combined the add and replace operations into one case.
             * [SO] 29.01.2025
             */
            case 'add':
            case 'replace':
                if(!$isSuperiorType) {
                    // Old EAV model logic
                    $alreadyModerated = AttributeValue::where('entity_id', $entity_id)
                        ->where('attribute_id', $attribute_id)
                        ->onlyModerated()
                        ->exists();

                    if($alreadyModerated && $user->isModerated()) {
                        $error = __('This attribute value is in moderation state. A user with appropriate permissions has to accept or deny it first.');
                        break;
                    }
                }
                
                $attrval = $modelClass::firstOrNew([
                    'entity_id' => $entity_id,
                    'attribute_id' => $attribute_id,
                ], [
                    'certainty' => null
                ]);
                
                if(!$isSuperiorType && $user->isModerated()) {
                    $attrval = $attrval->moderate('pending', false, true);
                    unset($attrval->comments_count);
                }
                break;
            default:
                $error = __('Unknown operation');
        }

        if($error !== null) {
            return [
                'message' => $error,
                'code' => $code,
            ];
        }

        // no further action required for deleted attribute values, continue with next patch
        if($operation == 'remove') {
            return false;
        }

        try {
            if($isSuperiorType) {
                // For superior types, set value directly
                $attrval->value = $attributeClass::serialize($value);
            } else {
                // For old EAV model, use the formatted key/value
                $formKeyValue = AttributeValue::getFormattedKeyValue($attr->datatype, $value);
                $attrval->{$formKeyValue->key} = $formKeyValue->val;
            }
        } catch(InvalidDataException $ide) {
            return [
                'message' => $ide->getMessage(),
                'code' => 422,
            ];
        }

        $attrval->user_id = $user->id;
        $attrval->save();

        // As we cannot ensure that the 'add' is correct,
        // we use this laravel option to ensure the attribute
        // was created and not replaced.
        if($attrval->wasRecentlyCreated) {
            $added[$attribute_id] = $attrval;
        }

        return false;
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

    public static function getFormattedKeyValue($datatype, $rawValue) : stdClass {
        $class = AttributeBase::getMatchingClass($datatype);
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
