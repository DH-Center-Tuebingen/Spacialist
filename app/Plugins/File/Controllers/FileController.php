<?php

namespace App\Plugins\File\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
    // GET
    public function init(Request $request)
    {
        info("init file");

        return response()->json([
            'status' => 'success',
        ]);
    }
}
