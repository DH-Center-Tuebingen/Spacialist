<?php

namespace App\Plugins\File\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\File\App\File;
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

    public function get(Request $request, $page = 1) {
        // $user = auth()->user();
        // if (!$user->can('view_files')) {
        //     return response()->json([
        //         'error' => __('You do not have the permission to view files')
        //     ], 403);
        // }
        $filters = $request->input('filters', []);
        $type = $request->input('t', 'all');
        $files = \App\Plugins\File\App\File::filter($page, [], $type);
        // $files = File::filter($page, [], $type);
        return response()->json($files);
    }
}
