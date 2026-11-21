<?php

namespace App\Preferences;

class MaintainerPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.project.maintainer';
    protected static string $label = 'project-maintainer';
    protected static string $id = 'project-maintainer-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::OPENACCESS;
}
