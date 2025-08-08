<?php

namespace App\Utils;

use Illuminate\Support\Str;

class UrlUtils {
    /**
     * When serving multiple applications (also already spacialist + thesaurex)
     * at the same domain, we need to separate the CSRF cookies.
     * This function extracts the subpath from the app URL
     * and returns it.
     *
     * @param string $url
     * @return string - Returns the subpath of the url with a leading '/'.
     *                  If there is no subpath present, '/' will be returned.
     *                  For Example https://spacialist.com/ancient-rome/ would result in '/ancient-rome'
     */
    static function getSubPath($url) : string {
        $matches = [];
        $doesMatch = preg_match('/https?:\/\/.+?\/(.*)/', $url, $matches);
        if(!$doesMatch || !isset($matches[1]) || empty(trim($matches[1]))) {
            return '/';
        }
        $subpathUrl = trim($matches[1]);
        return Str::start(trim($subpathUrl, '/'), '/');
    }
}
