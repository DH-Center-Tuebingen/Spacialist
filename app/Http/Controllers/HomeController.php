<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Context;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $var = Context::with(['child_contexts', 'context_type'])->whereNull('root_context_id')->take(20)->get();
        // $var = [
        //     [
        //         'name' => 'Testcontext'
        //     ]
        // ];
        return view('home', ['contexts' => $var]);
    }

    public function prefs()
    {
        return view('settings.preferences');
    }

    public function userPrefs($id)
    {
        \Log::info("Getting here with $id");
        return view('settings.userpreferences');
    }

    public function users()
    {
        return view('settings.users');
    }

    public function roles()
    {
        return view('settings.roles');
    }

    public function dme()
    {
        return view('settings.editor.dme');
    }

    public function layer()
    {
        return view('settings.editor.layer');
    }

    public function gis()
    {
        return view('tools.gis');
    }

    public function bibliography()
    {
        return view('tools.bibliography');
    }

    public function analysis()
    {
        return view('tools.analysis');
    }
}
