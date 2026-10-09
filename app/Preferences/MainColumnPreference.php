<?php

namespace App\Preferences;

class MainColumnPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.columns.title';
    protected static string $label = 'columns';
    protected static string $id = 'columns-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::USER, Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::INTERFACE;
}
