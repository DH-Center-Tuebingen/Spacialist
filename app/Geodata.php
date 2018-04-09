<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Geometries\Geometry;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Exceptions\UnknownWKTTypeException;

class Geodata extends Model
{
    use PostgisTrait;

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

    const availableGeometryTypes = [
        'Point', 'LineString', 'Polygon'
    ];

    public function updateGeometry($feature, $srid) {
        $geom = json_encode($feature->geometry);
        $wkt = \DB::select("SELECT ST_AsText(ST_Transform(ST_GeomFromText(ST_AsText(ST_GeomFromGeoJSON('$geom')), $srid), 4326)) AS wkt")[0]->wkt;
        $this->geom = self::parseWkt($wkt);
        $this->lasteditor = 'Admin'; // TODO
        $this->save();
    }

    public static function createFromFeatureCollection($collection, $srid) {
        $objs = [];
        foreach($collection->features as $feature) {
            $geom = json_encode($feature->geometry);
            // ST_GeomFromGeoJSON doesn't support srid...
            $wkt = \DB::select("SELECT ST_AsText(ST_Transform(ST_GeomFromText(ST_AsText(ST_GeomFromGeoJSON('$geom')), $srid), 4326)) AS wkt")[0]->wkt;
            $geodata = new self();
            $geodata->geom = self::parseWkt($wkt);
            $geodata->lasteditor = 'Admin'; // TODO
            $geodata->save();
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
            return -1;
        }
    }

    public function context() {
        return $this->hasOne('App\Context');
    }
}
