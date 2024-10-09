<?php

namespace App;

use Clickbar\Magellan\Data\Geometries\Geometry;
use Clickbar\Magellan\Database\Eloquent\HasPostgisColumns;
use Clickbar\Magellan\IO\Parser\WKT\WKTParser;
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

    /**
     * Parses a WKT string into a Geometry object
     * if the WKT string is invalid, an exception is thrown.
     * 
     * @param string $wkt
     * @return Geometry
     * @throws \Clickbar\Magellan\Exceptions\InvalidWKTException
     */
    public static function parseWkt(string $wkt) :Geometry {
        $parser = app(WKTParser::class);
        return $parser->parse($wkt);
    }

    public static function arrayToWkt($arr) {
        $json = json_encode($arr);
        return DB::select("SELECT ST_AsText(ST_GeomFromGeoJSON('$json')) AS wkt")[0]->wkt;

    }
}
