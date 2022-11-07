<?php

namespace App;

use MStaack\LaravelPostgis\Geometries\Geometry;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;
use MStaack\LaravelPostgis\Exceptions\UnknownWKTTypeException;

class Geodata
{
    use PostgisTrait;

    protected static $availableGeometryTypes = [
        'Point', 'LineString', 'Polygon', 'MultiPoint', 'MultiLineString', 'MultiPolygon'
    ];

    public static function getAvailableGeometryTypes() {
        return self::$availableGeometryTypes;
    }

    public static function parseWkt($wkt) {
        try {
            $geom = Geometry::getWKTClass($wkt);
            $parsed = $geom::fromWKT($wkt);
            return $parsed;
        } catch(UnknownWKTTypeException $e) {
            return null;
        }
    }
}
