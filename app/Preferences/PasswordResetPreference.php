<?php

namespace App\Preferences;

class PasswordResetPreference extends PreferenceBase {
    protected static string $title = 'main.preference.key.password_reset';
    protected static string $label = 'enable-password-reset-link';
    protected static string $id = 'reset-email-preference';
    /** @var list<Category> */
    protected static array $categories = [Category::SYSTEM];
    protected static SubCategory $subCategory = SubCategory::GENERAL;
}
