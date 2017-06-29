<?php

namespace App\Http\Controllers;
use Log;
use App\User;
use \DB;
use Illuminate\Http\Request;

class AnalysisController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getAnalyses() {
        return response()->json(
            DB::table('stored_queries')
                ->get()
        );
    }

    // POST

    // PATCH

    // PUT
    
    // DELETE
}
