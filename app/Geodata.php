<?php

namespace App;

use Clickbar\Magellan\Data\Geometries\Geometry;
use Clickbar\Magellan\IO\Generator\WKB\WKBGenerator;
use Clickbar\Magellan\IO\Generator\WKT\WKTGenerator;
use Clickbar\Magellan\IO\Parser\WKB\WKBParser;
use Clickbar\Magellan\IO\Parser\WKT\WKTParser;
use Illuminate\Support\Facades\DB;
use Exception;

class Geodata
{
    protected static array $availableGeometryTypes = [
        'Point', 'LineString', 'Polygon', 'MultiPoint', 'MultiLineString', 'MultiPolygon'
    ];

    public static function getAvailableGeometryTypes(): array {
        return self::$availableGeometryTypes;
    }

    public static function fromWKB(string $wkb): ?Geometry {
        try {
            $parser = app(WKBParser::class);
            return $parser->parse($wkb);
        } catch(Exception $e) {
            return null;
        }
    }

    public static function fromWKT(string $wkt): ?Geometry {
        try {
            $parser = app(WKTParser::class);
            // Format: SRID=4326;POINT (2, 2)
            return $parser->parse($wkt);
        } catch(Exception $e) {
            return null;
        }
    }

    public static function toWKB(Geometry $geometry): string {
        return (new WKBGenerator())->generate($geometry);
    }

    public static function toWKT(Geometry $geometry): string {
        return (new WKTGenerator())->generate($geometry);
    }

    public static function wkt2wkb(string $wkt): string {
        return self::toWKB(self::fromWKT($wkt));
    }

    public static function wkb2wkt(string $wkb): string {
        return self::toWKT(self::fromWKB($wkb));
    }

    public static function arrayToWKT(array $arr): string {
        $json = json_encode($arr);
        return DB::select("SELECT ST_AsText(ST_GeomFromGeoJSON('$json')) AS wkt")[0]->wkt;
    }
}
