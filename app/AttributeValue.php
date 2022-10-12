<?php

namespace App;

use App\Exceptions\InvalidDataException;
use Illuminate\Database\Eloquent\Model;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;
use App\Traits\CommentTrait;
use App\Traits\ModerationTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class AttributeValue extends Model implements Searchable
{
    use PostgisTrait;
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

    protected $postgisFields = [
        'geography_val',
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
        return $this->str_val ??
               $this->int_val ??
               $this->dbl_val ??
               $this->entity_val ??
               $this->thesaurus_val ??
               json_decode($this->json_val) ??
               $this->dt_val ??
               $this->geography_val->toWKT();
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

    // Does not handle InvalidDataException and AmbiguousValueException in stringToValue method here!
    // Throws InvalidDataException
    // Throws AmbiguousValueException
    public function setValue($strValue, $type = null, $save = false) {
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
        $column = '';
        switch($type) {
            case 'string':
            case 'stringf':
            case 'iconclass':
            case 'rism':
                $column = 'str_val';
                break;
            case 'double':
                $column = 'dbl_val';
                break;
            case 'integer':
            case 'boolean':
            case 'percentage':
                $column = 'int_val';
                break;
            case 'string-sc':
                $column = 'thesaurus_val';
                break;
            case 'date':
                $column = 'dt_val';
                break;
            case 'string-mc':
            case 'epoch':
            case 'timeperiod':
            case 'dimension':
            case 'list':
            case 'table':
            case 'entity-mc':
                $column = 'json_val';
                break;
            case 'geography':
                $column = 'geography_val';
                break;
            case 'entity':
                $column = 'entity_val';
                break;
            case 'sql':
            case 'serial':
                $column = null;
                break;
        }

        return $column;
    }

    // Throws InvalidDataException
    // Throws AmbiguousValueException
    public static function stringToValue($strValue, $type) {
        if(!isset($strValue) || trim($strValue) === '') return null;

        $trimmedVal = trim($strValue);
        $value = null;

        switch($type) {
            case 'string':
            case 'stringf':
            case 'date':
            case 'geography':
            case 'iconclass':
            case 'rism':
                $value = $trimmedVal;
                break;
            case 'double':
                if(!is_numeric($trimmedVal)) {
                    throw new InvalidDataException("Given data is not a number");
                }
                $value = floatval($trimmedVal);
                break;
            case 'integer':
            case 'percentage':
                if(!is_int($trimmedVal)) {
                    throw new InvalidDataException("Given data is not an integer");
                }
                $value = intval($trimmedVal);
                break;
            case 'boolean':
                $value = $trimmedVal == 1 ||
                          $trimmedVal == '1' ||
                          strtolower($trimmedVal) == 'true' ||
                          strtolower($trimmedVal) == 't' ||
                          intval($trimmedVal) > 0;
                break;
            case 'string-sc':
                $concept = ThConcept::getByString($trimmedVal);
                if(isset($concept)) {
                    $value = $concept->concept_url;
                } else {
                    throw new InvalidDataException("Given data is not a valid concept/label in the vocabulary");
                }
                break;
            case 'string-mc':
                $convValues = [];
                $parts = explode(';', $trimmedVal);
                foreach($parts as $part) {
                    $trimmedPart = trim($part);
                    $concept = ThConcept::getByString($trimmedPart);
                    if(isset($concept)) {
                        $convValues[] = [
                            'id' => $concept->id,
                            'concept_url' => $concept->concept_url,
                        ];
                    } else {
                        throw new InvalidDataException("Given data part ($trimmedPart) is not a valid concept/label in the vocabulary");
                    }
                }
                $value = json_encode($convValues);
                break;
            case 'epoch':
                $startLabel = 'ad';
                $endLabel = 'ad';
                $parts = explode(';', $trimmedVal);

                if(count($parts) != 3) {
                    throw new InvalidDataException("Given data does not match this datatype's format (START;END;CONCEPT)");
                }

                $start = intval(trim($parts[0]));
                $end = intval(trim($parts[1]));

                if($end < $start) {
                    throw new InvalidDataException("Start date must not be after end data ($start, $end)");
                }
                
                $concept = ThConcept::getByString(trim($parts[2]));
                $epoch = null;
                if(isset($concept)) {
                    $epoch = [
                        'id' => $concept->id,
                        'concept_url' => $concept->concept_url,
                    ];
                } else {
                    throw new InvalidDataException("Given data is not a valid concept/label in the vocabulary");
                }
                if($start < 0) {
                    $startLabel = 'bc';
                    $start = abs($start);
                }
                if($end < 0) {
                    $endLabel = 'bc';
                    $end = abs($end);
                }
                $value = json_encode([
                    'start' => $start,
                    'startLabel' => $startLabel,
                    'end' => $end,
                    'endLabel' => $endLabel,
                    'epoch' => $epoch,
                ]);
                break;
            case 'timeperiod':
                $startLabel = 'ad';
                $endLabel = 'ad';
                $parts = explode(';', $trimmedVal);

                if(count($parts) != 2) {
                    throw new InvalidDataException("Given data does not match this datatype's format (START;END)");
                }

                $start = intval(trim($parts[0]));
                $end = intval(trim($parts[1]));

                if($end < $start) {
                    throw new InvalidDataException("Start date must not be after end data ($start, $end)");
                }

                if($start < 0) {
                    $startLabel = 'bc';
                    $start = abs($start);
                }
                if($end < 0) {
                    $endLabel = 'bc';
                    $end = abs($end);
                }
                $value = json_encode([
                    'start' => $start,
                    'startLabel' => $startLabel,
                    'end' => $end,
                    'endLabel' => $endLabel,
                ]);
                break;
            case 'dimension':
                $parts = explode(';', $trimmedVal);

                if(count($parts) != 4) {
                    throw new InvalidDataException("Given data does not match this datatype's format (VAL1;VAL2;VAL3;UNIT)");
                }

                $value = json_encode([
                    'B' => floatval(trim($parts[0])),
                    'H' => floatval(trim($parts[1])),
                    'T' => floatval(trim($parts[2])),
                    'unit' => trim($parts[3]),
                ]);
                break;
            case 'list':
                $trimmedValues = [];
                $parts = explode(';', $trimmedVal);
                foreach($parts as $part) {
                    $trimmedValues[] = trim($part);
                }
                $value = json_encode($trimmedValues);
                break;
            case 'entity-mc':
                $idList = [];
                $parts = explode(';', $trimmedVal);
                foreach($parts as $part) {
                    $trimmedPart = trim($part);

                    $entityId = Entity::getFromPath($trimmedPart);
                    $idList[] = $entityId;
                }
                $value = json_encode($idList);
                break;
            case 'entity':
                $entityId = Entity::getFromPath($trimmedVal);
                $value = $entityId;
                break;
            case 'table':
            case 'sql':
            case 'serial':
                $value = null;
                break;
        }

        return $value;
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
