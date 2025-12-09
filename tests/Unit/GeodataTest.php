<?php

use App\Geodata;
use Clickbar\Magellan\Data\Geometries\Dimension;

test('from wkt with typo', function () {
    $typoData = Geodata::fromWKT('PIONT(1 2)');
    expect($typoData)->toBeNull();
});

test('from wkt with3d point', function () {
    $point3d = Geodata::fromWKT('POINT Z(1 2 3)');
    expect($point3d->getX())->toEqual(1);
    expect($point3d->getY())->toEqual(2);
    expect($point3d->getZ())->toEqual(3);
    expect($point3d->getDimension())->toEqual(Dimension::DIMENSION_3DZ);
    expect($point3d->hasSrid())->toBeFalse();
});

test('from wkt with2d point and srid', function () {
    // FIXME: currently Magellan uses a Singleton/Service Container approach which leads to
    // a problem in case an unsupported/wrong WKT-Type is provided
    // For this wrong type the singleton's $dimension is set to 2D
    // if later a non-2D-WKT-type is provided, a Exception is thrown, because
    // the internal $dimension of the singleton is still 2D and does (of course)
    // not match e.g. 3DZ
    // $typoData = Geodata::fromWKT('PIONT(1 2)');
    // $this->assertNull($typoData);
    $sridPoint2d = Geodata::fromWKT('SRID=4326;POINT(1 2)');
    expect($sridPoint2d->getLongitude())->toEqual(1);
    expect($sridPoint2d->getLatitude())->toEqual(2);
    expect($sridPoint2d->getAltitude())->toBeNull();
    expect($sridPoint2d->getDimension())->toEqual(Dimension::DIMENSION_2D);
    expect($sridPoint2d->hasSrid())->toBeTrue();
});
