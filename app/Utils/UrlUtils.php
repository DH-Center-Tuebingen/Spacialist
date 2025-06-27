<?php

namespace App\Utils;

class UrlUtils {
    /**
     * When serving multiple applications (also already spacialist + thesaurex)
     * at the same domain, we need to separate the CSRF cookies.
     * This function extracts the subpath from the app URL
     * and returns it.
     */
    static function getSubPath() : string {
        $matches = [];
        $doesMatch = preg_match('/https?:\/\/.+?\/(.*)/', config('app.url'), $matches);
        
        if(!$doesMatch || !isset($matches[1]) || empty(trim($matches[1]))) {
            return '/';
        }
        return trim($matches[1]);
    }
}
