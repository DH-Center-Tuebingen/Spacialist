<?php

namespace App\Http\Controllers;
use App\AvailableLayer;
use Illuminate\Http\Request;

class OverlayController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getOverlays() {
        return response()->json([
            'layers' => AvailableLayer::all()
        ]);
    }

    // POST

    // PATCH

    // PUT

    // DELETE
}
