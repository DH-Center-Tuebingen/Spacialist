<?php

namespace App;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Exceptions\UnknownWKTTypeException;

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

    public static function sortMatchesDesc($a, $b) {
        if($a['count'] == $b['count']) return 0;
        return $a['count'] > $b['count'] ? -1 : 1;
    }
}
