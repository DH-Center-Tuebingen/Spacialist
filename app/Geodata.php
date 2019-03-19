<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Exceptions\UnknownWKTTypeException;
use Nicolaslopezj\Searchable\SearchableTrait;

class Geodata extends Model
{
    use PostgisTrait;
    use SearchableTrait;

    protected $table = 'geodata';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'color',
    ];

    protected $postgisFields = [
        'geom',
    ];

    protected $searchable = [
        'columns' => [
            'ST_AsText(geom)' => 10,
        ]
    ];

    protected static $availableGeometryTypes = [
        'Point', 'LineString', 'Polygon', 'MultiPoint', 'MultiLineString', 'MultiPolygon'
    ];

    public static function getAvailableGeometryTypes() {
        return self::$availableGeometryTypes;
    }

    public function patch($geometryAsStr, $srid, $user) {
        $wkt = \DB::select("SELECT ST_AsText(ST_Transform(ST_GeomFromText(ST_AsText(ST_GeomFromGeoJSON('$geometryAsStr')), $srid), 4326)) AS wkt")[0]->wkt;
        $parsedWkt = self::parseWkt($wkt);
        if(!isset($parsedWkt)) return;
        $this->geom = $parsedWkt;
        $this->lasteditor = $user->name;
        $this->save();
    }

    public static function createFromFeatureCollection($collection, $srid, $user) {
        $objs = [];
        foreach($collection->features as $feature) {
            $geom = json_encode($feature->geometry);
            // ST_GeomFromGeoJSON doesn't support srid...
            $wkt = \DB::select("SELECT ST_AsText(ST_Transform(ST_GeomFromText(ST_AsText(ST_GeomFromGeoJSON('$geom')), $srid), 4326)) AS wkt")[0]->wkt;
            $parsedWkt = self::parseWkt($wkt);
            if(!isset($parsedWkt)) continue;
            $geodata = new self();
            $geodata->geom = $parsedWkt;
            $geodata->lasteditor = $user->name;
            $geodata->save();
            $geodata->entity;
            $objs[] = $geodata;
        }
        return $objs;
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

    public function entity() {
        return $this->hasOne('App\Entity');
    }
}
