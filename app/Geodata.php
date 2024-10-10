<?php

namespace App;

use Clickbar\Magellan\Data\Geometries\Geometry;
use Clickbar\Magellan\Database\Eloquent\HasPostgisColumns;
use Illuminate\Support\Facades\DB;

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
        // Fixed version in 0.11-feat-showcase
        $geom = Geometry::getWKTClass($wkt);
        $parsed = $geom::fromWKT($wkt);
        return $parsed;
    }

    public static function arrayToWkt($arr) {
        $json = json_encode($arr);
        return DB::select("SELECT ST_AsText(ST_GeomFromGeoJSON('$json')) AS wkt")[0]->wkt;

    }

    public static function arrayToWkt($arr) {
        $json = json_encode($arr);
        return DB::select("SELECT ST_AsText(ST_GeomFromGeoJSON('$json')) AS wkt")[0]->wkt;

    }
}
