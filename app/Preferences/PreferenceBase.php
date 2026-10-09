<?php

namespace App\Preferences;

abstract class PreferenceBase {
    protected static string $title;
    protected static string $label;
    protected static string $id;
    protected static ?array $isHiding;
    /** @var list<Category> */
    protected static array $categories;
    protected static SubCategory $subCategory;

    public static function serialized(): array {
        $serializedData = [
            'title' => static::$title,
            'label' => static::$label,
            'id' => static::$id,
            'categories' => array_map(function($category) {
                return $category->value;
            }, static::$categories),
            'subcategory' => static::$subCategory->value,
        ];

        if(static::canHide()) {
            $serializedData['isHiding'] = static::$isHiding;
        }

        return $serializedData;
    }

    public static function getType(): string {
        return static::$type;
    }

    public static function getLabel(): string {
        return static::$label;
    }

    public static function getId(): string {
        return static::$id;
    }

    public static function canHide(): bool {
        return isset(static::$isHiding);
    }

    public static function getHiding(): ?array {
        return static::canHide() ? static::$isHiding : null;
    }

    public static function getCategories(): array {
        return static::$categories;
    }

    public static function getSubCategory(): SubCategory {
        return static::$subCategory;
    }

    public static function hasCategory(PreferenceBase $preference, Category $category): bool {
        return in_array($category, $preference::$categories, true);
    }
}
