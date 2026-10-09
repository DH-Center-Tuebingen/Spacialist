<?php

namespace App\Preferences;

enum SubCategory: string {
    case GENERAL = 'general';
    case LAYOUT = 'layout';
    case INTERFACE = 'interface';
    case OPENACCESS = 'openaccess';
}