<?php

namespace App\Preferences;

class LinkToThesaurexPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.link_thesaurex';
    protected static string $label = 'link-to-thesaurex';
    protected static string $id = 'thesaurus-link-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::GENERAL;
}
