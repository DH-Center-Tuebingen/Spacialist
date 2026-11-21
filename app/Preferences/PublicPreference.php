<?php

namespace App\Preferences;

class PublicPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.project.public';
    protected static string $label = 'enable-open-access';
    protected static string $id = 'open-access-preference';
    protected static ?array $isHiding = [
        'type' => 'subcategory',
        'name' => SubCategory::OPENACCESS,
        'condition' => false,
    ];
    /** @var list<Category> */
    protected static array $categories = [Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::GENERAL;
}
