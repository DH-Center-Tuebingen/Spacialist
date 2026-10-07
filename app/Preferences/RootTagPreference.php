<?php

namespace App\Preferences;

class RootTagPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.tag_root';
    protected static string $label = 'tag_root';
    protected static string $id = 'tags-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::GENERAL;
}
