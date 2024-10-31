<?php

namespace App;

use Clickbar\Magellan\Database\Eloquent\HasPostgisColumns;
use Clickbar\Magellan\IO\Parser\WKT\WKTParser;
use Exception;
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
        try {
            $parser = app(WKTParser::class);
            $parsed = $parser->parse($wkt);
            return $parsed;
        } catch(Exception $e) {
            return null;
        }
    }

    public static function arrayToWkt($arr) {
        $json = json_encode($arr);
        return DB::select("SELECT ST_AsText(ST_GeomFromGeoJSON('$json')) AS wkt")[0]->wkt;

    }
}
