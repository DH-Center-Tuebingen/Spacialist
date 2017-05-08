<?php

namespace App\Http\Controllers;
use App\AvailableLayer;
use Illuminate\Http\Request;

class OverlayController extends Controller {
    public $availableGeometryTypes = [
        'Point', 'Linestring', 'Polygon', 'Multipoint', 'Multilinestring', 'Multipolygon'
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    public function getAll() {
        return response()->json([
            'layers' => AvailableLayer::orderBy('position', 'asc')->get()
        ]);
    }
}
