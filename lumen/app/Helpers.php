<?php

namespace App;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Exceptions\UnknownWKTTypeException;
use App\Literature;

class Helpers {
    public static function startsWith($haystack, $needle) {
        return strtoupper(substr($haystack, 0, strlen($needle))) === strtoupper($needle);
    }

    public static function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if($length === 0) return true;
        return strtoupper(substr($haystack, -$length)) === strtoupper($needle);
    }

    public static function parseWkt($wkt) {
        try {
            $geom = Geometry::getWKTClass($wkt);
            $parsed = $geom::fromWKT($wkt);
            return $parsed;
        } catch(UnknownWKTTypeException $e) {
            return -1;
        }
    }

    public static function computeCitationKey($l) {
        $key;
        if($l['author'] != null) {
            $key = $l['author'];
        } else {
            $key = $l['title'];
        }
        // Use first two letters of author/title as key with only first letter uppercase
        $key = ucwords(mb_strtolower(substr($key, 0, 2))) . ':';
        if($l['year'] != null) {
            $key .= $l['year'];
        } else {
            $key .= '0000';
        }

        $initalKey = $key;
        $suffixes = array_merge(range('a', 'z'), range('A', 'Z'));
        $suffixesCount = count($suffixes);
        $i = 0;
        $j = 0;
        while(Literature::where('citekey', $key)->first() !== null) {
            // if single letter was not enough to be unique, add another
            if($i == $suffixesCount) {
                if($j == $suffixesCount) $j = 0;
                $initalKey = $initalKey . $suffixes[$j++];
                $i = 0;
            }
            $key = $initalKey . $suffixes[$i++];
        }
        return $key;
    }

    public static function sortMatchesDesc($a, $b) {
        if($a['count'] == $b['count']) return 0;
        return $a['count'] > $b['count'] ? -1 : 1;
    }

    public static function parseBoolean($str) {
        $acceptable = [true, 1, '1', 'true', 'TRUE'];
        return in_array($str, $acceptable, true);
    }
}
