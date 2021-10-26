<?php

namespace App\Http\Controllers;

use App\File;
use App\Entity;
use App\FileTag;
use App\Preference;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
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

    // GET

    public function getFile($id) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view a specific file')
            ], 403);
        }
        try {
            $file = File::getFileById($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }
        return response()->json($file);
    }

    public function getSubFiles(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view successors of a specific file')
            ], 403);
        }
        try {
            Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $category = $request->query('c');
        $subFiles = File::getSubFiles($id, $category);

        return response()->json($subFiles);
    }

    public function getLinkCount($id) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to get number of links of a specific file')
            ], 403);
        }
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }
        return response()->json($file->linkCount());
    }

    // POST

    public function getFiles(Request $request, $page = 1) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view files')
            ], 403);
        }
        $filters = $request->input('filters', []);
        $files = File::getAllPaginate($page, $filters);
        return response()->json($files);
    }

    public function getUnlinkedFiles(Request $request, $page = 1) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view files')
            ], 403);
        }
        $filters = $request->input('filters', []);
        $files = File::getUnlinkedPaginate($page, $filters);
        return response()->json($files);
    }

    public function getLinkedFiles(Request $request, $cid, $page = 1) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view files')
            ], 403);
        }
        $filters = $request->input('filters', []);
        $files = File::getLinkedPaginate($cid, $page, $filters);
        return response()->json($files);
    }

    public function patchContent(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('manage_files')) {
            return response()->json([
                'error' => __('You do not have the permission to edit a file\'s content')
            ], 403);
        }
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }

        $file->setContent($request->file('file'));
        $file->setFileInfo();

        return response()->json($file);
    }

    public function exportFiles(Request $request) {
        $user = auth()->user();
        if(!$user->can('export_files')) {
            return response()->json([
                'error' => __('You do not have the permission to export files')
            ], 403);
        }
        $this->validate($request, [
            'files' => 'required|array'
        ]);

        $ids = $request->input('files', []);
        $files = File::whereIn('id', $ids)->get();
        $archive = File::createArchiveFromList($files);
        // get raw parsed content
        $content = file_get_contents($archive['path']);
        // delete tmp file
        unlink($archive['path']);
        // return response()->streamDownload(function() use ($content) {
        //     echo $content;
        // }, 'export.zip', [
        //     'Content-Type' => $archive['type']
        // ]);
        return response(base64_encode($content))->header('Content-Type', $archive['type']);
    }

    // PATCH

    public function patchProperty(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('manage_files')) {
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
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }

        if($request->has('name')) {
            $newName = $request->get('name');
            $otherFileWithName = File::where('name', $newName)->first();
            if(
                (isset($otherFileWithName) && $otherFileWithName->id != $id)
                ||
                $file->rename($newName) === false
            ) {
                return response()->json([
                    'error' => __('There is already a file with this name')
                ], 400);
            }
        }

        foreach($request->only(['copyright', 'description']) as $key => $value) {
            $file->{$key} = $value;
        }
        $file->save();
        $file->setFileInfo();

        return response()->json($file);
    }
}
