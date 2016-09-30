<?php

namespace App\Http\Controllers;
use App\User;
use \DB;
use Illuminate\Http\Request;

class LiteratureController extends Controller
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

    public function getAll() {
        return response()->json(
            DB::table('bib_tex')
            ->orderBy('id', 'asc')
            ->get()
        );
    }
}
