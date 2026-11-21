<?php

namespace App\Preferences;

class LanguagePreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.language';
    protected static string $label = 'gui-language';
    protected static string $id = 'gui-language-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::USER, Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::LAYOUT;
}
