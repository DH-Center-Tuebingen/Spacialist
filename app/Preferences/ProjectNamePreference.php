<?php

namespace App\Preferences;

class ProjectNamePreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.project.name';
    protected static string $label = 'project-name';
    protected static string $id = 'project-name-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::GENERAL;
}
