<?php

namespace App\Plugins\File\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\File\App\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $user = auth()->user();
        if (!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view files')
            ], 403);
        }
        $filters = $request->input('filters', []);
        $type = $request->input('t', 'all');
        $files = File::filter($page, [], $type);
        // $files = File::filter($page, [], $type);
        return response()->json($files);
    }

    // PATCH

    public function patchProperty(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->can('manage_files')) {
            return response()->json([
                'error' => __('You do not have the permission to modify file properties')
            ], 403);
        }
        $this->validate($request, [
            'copyright' => 'nullable|string',
            'description' => 'nullable|string',
            'name' => 'string'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }

        if ($request->has('name')) {
            $newName = $request->get('name');
            $otherFileWithName = File::where('name', $newName)->first();
            if (
                (isset($otherFileWithName) && $otherFileWithName->id != $id)
                ||
                Storage::exists($newName)
            ) {
                return response()->json([
                    'error' => __('There is already a file with this name')
                ], 400);
            }
            $file->rename($newName);
        }

        foreach ($request->only(['copyright', 'description']) as $key => $value) {
            $file->{$key} = $value;
        }
        $file->save();
        $file->setFileInfo();

        return response()->json($file);
    }
}
