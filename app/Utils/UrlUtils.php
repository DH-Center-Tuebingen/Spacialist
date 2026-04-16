<?php

namespace App\Utils;

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
    static function getSubPath(string $url) : string {
        $matches = [];
        // Get the string after the domain entry.
        $doesMatch = preg_match('/https?:\/\/.+?\/(.*)/', $url, $matches);
        if(!$doesMatch || !isset($matches[1])) {
            return '/';
        }
        $trimDefaultCharacters = " \n\r\t\v\0";
        // Remove all irrelevant characters from the subpath
        // including all leading and trailing slashes.
        $subPathUrl = trim($matches[1], $trimDefaultCharacters . '/');
        if(empty($subPathUrl)) {
            return '/';
        }

        // Add leading slash to the subpath.
        return  '/' . $subPathUrl;
    }
}
