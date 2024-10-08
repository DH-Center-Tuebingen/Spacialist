<?php

namespace App;

use Illuminate\Support\Facades\DB;
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

    public static function arrayToWkt($arr) {
        $json = json_encode($arr);
        return DB::select("SELECT ST_AsText(ST_GeomFromGeoJSON('$json')) AS wkt")[0]->wkt;

    }
}
