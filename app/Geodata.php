<?php

namespace App;

use Clickbar\Magellan\Data\Geometries\Geometry;
use Clickbar\Magellan\Database\Eloquent\HasPostgisColumns;
use MStaack\LaravelPostgis\Exceptions\UnknownWKTTypeException;

class Geodata
{
    use HasPostgisColumns;

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
