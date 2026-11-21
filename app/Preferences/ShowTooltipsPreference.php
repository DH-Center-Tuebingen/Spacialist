<?php

namespace App\Preferences;

class ShowTooltipsPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.tooltips';
    protected static string $label = 'show-tooltips';
    protected static string $id = 'tooltips-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::USER, Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::INTERFACE;
}
