<?php

namespace App\Preferences;

class ColorPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.color.title';
    protected static string $label = 'color';
    protected static string $id = 'color-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::USER, Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::LAYOUT;
}
