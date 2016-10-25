<?php

namespace App\Http\Controllers;
use App\User;
use \DB;
use Illuminate\Http\Request;

class GpsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getMarker($id) {

    }

    public function getMarkers() {
        return response()->json(DB::table('finds')->get()->first());
    }
}
