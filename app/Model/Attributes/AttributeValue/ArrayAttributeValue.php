<?php

namespace App\Model\Attributes\AttributeValue;

use App\Attribute;
use App\AttributeTypes\AttributeBase;
use App\Exceptions\InvalidDataException;
use App\Model\Attributes\AttributeValue\TypedValues\IntegerAttributeValue;
use App\Model\Attributes\AttributeValue\TypedValues\DoubleAttributeValue;
use App\Model\Attributes\AttributeValue\TypedValues\StringAttributeValue;
use App\Model\Attributes\AttributeValue\TypedValues\BooleanAttributeValue;
use App\Model\Attributes\AttributeValue\TypedValues\EntityAttributeValue;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ArrayAttributeValue extends AttributeValue
{
    protected $table = 'array_attribute_values';

    protected $fillable = [
        'attribute_id',
        'entity_id',
        'certainty',
        'user_id',
    ];

    /**
     * Get all array items (ordered by position).
     */
    public function items(): HasMany
    {
        return $this->hasMany(ArrayItem::class, 'array_attribute_value_id')
                    ->orderBy('position');
    }

    /**
     * Get the serialized array of values.
     */
    public function getValue()
    {
        return $this->items()
            ->with('item')
            ->get()
            ->map(fn($item) => $item->getValue())
            ->values()
            ->toArray();
    }

    /**
     * Set values from raw input (expects array).
     * 
     * @param array $rawValue Array of values
     * @param string|null $type The item type (from attribute definition)
     * @param bool $save Whether to save the model
     * @return array The processed values
     * @throws InvalidDataException
     */
    public function setValueFromRaw($rawValue, $type = null, $save = false)
    {
        if(!is_array($rawValue)) {
            throw new InvalidDataException('Array attribute value must be an array');
        }

        // Get item type from attribute if not provided
        if($type === null && $this->attribute) {
            $type = $this->attribute->datatype;
        }

        if($type === null) {
            throw new InvalidDataException('Cannot determine item type for array');
        }

        // Extract the base type (remove 'list-' prefix if present)
        $itemType = str_replace('list-', '', $type);

        if($save) {
            $this->save();
            $this->setItems($rawValue, $itemType);
        }

        return $rawValue;
    }

    /**
     * Set array items, replacing existing ones.
     * 
     * @param array $values
     * @param string $itemType The datatype of items (e.g., 'string-sc', 'integer', 'double')
     */
    public function setItems(array $values, string $itemType): void
    {
        DB::transaction(function() use ($values, $itemType) {
            // Delete existing items and their typed values
            $this->deleteItems();

            // Create new items
            foreach($values as $position => $value) {
                $this->addItem($value, $position, $itemType);
            }
        });
    }

    /**
     * Add a single item to the array.
     * 
     * @param mixed $value
     * @param int $position
     * @param string $itemType
     * @return ArrayItem
     */
    public function addItem($value, int $position, string $itemType): ArrayItem
    {
        $typedValue = $this->createTypedValue($value, $itemType);

        return ArrayItem::create([
            'array_attribute_value_id' => $this->id,
            'item_id' => $typedValue->id,
            'item_type' => get_class($typedValue),
            'position' => $position,
        ]);
    }

    /**
     * Delete all items and their typed values.
     */
    public function deleteItems(): void
    {
        $items = $this->items()->with('item')->get();
        
        foreach($items as $item) {
            // Delete the typed value
            $item->item?->delete();
        }

        // Delete the array items
        $this->items()->delete();
    }

    /**
     * Create a typed value based on the item type.
     * 
     * @param mixed $value
     * @param string $itemType
     * @return Model
     * @throws InvalidDataException
     */
    protected function createTypedValue($value, string $itemType)
    {
        // Use AttributeBase to unserialize the value properly
        $class = AttributeBase::getMatchingClass($itemType);
        
        if($class === false) {
            throw new InvalidDataException("Unknown item type: {$itemType}");
        }

        $field = $class::getField();
        $unserializedValue = $class::unserialize($value);

        // Map field names to typed value models
        return match($field) {
            'int_val' => IntegerAttributeValue::createValue($unserializedValue),
            'dbl_val' => DoubleAttributeValue::createValue($unserializedValue),
            'str_val' => StringAttributeValue::createValue($unserializedValue),
            'entity_val' => EntityAttributeValue::createValue($unserializedValue),
            // Add more mappings as needed
            default => throw new InvalidDataException("Unsupported field type: {$field}"),
        };
    }

    /**
     * Static helper to create or update an array attribute value.
     * 
     * @param int $entityId
     * @param int $attributeId
     * @param array $values
     * @param int $userId
     * @param int|null $certainty
     * @return ArrayAttributeValue
     */
    public static function createOrUpdate(int $entityId, int $attributeId, array $values, int $userId, ?int $certainty = null)
    {
        return DB::transaction(function() use ($entityId, $attributeId, $values, $userId, $certainty) {
            $attrValue = static::firstOrNew([
                'entity_id' => $entityId,
                'attribute_id' => $attributeId,
            ]);

            $attrValue->user_id = $userId;
            
            if($certainty !== null) {
                $attrValue->certainty = $certainty;
            }

            $attrValue->save();

            // Get the item type from the attribute
            $attribute = Attribute::find($attributeId);
            $itemType = str_replace('list-', '', $attribute->datatype);

            $attrValue->setItems($values, $itemType);

            return $attrValue;
        });
    }

    /**
     * Override delete to clean up typed values.
     */
    public function delete()
    {
        $this->deleteItems();
        return parent::delete();
    }
}