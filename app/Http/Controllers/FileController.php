<?php

namespace App\Http\Controllers;

use App\File;
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

    public function getFiles($page = 1) {
        $files = File::getAllPaginate($page);
        return response()->json($files);
    }

    public function getUnlinkedFiles($page = 1) {
        $files = File::getUnlinkedPaginate($page);
        return response()->json($files);
    }

    public function getLinkedFiles($cid, $page = 1) {
        $files = File::getLinkedPaginate($cid, $page);
        return response()->json($files);
    }

    public function getArchiveFileList($id) {
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }
        $content = $file->getArchiveFileList();
        return response()->json($content);

    }

    public function downloadArchivedFile(Request $request, $id) {
        $this->validate($request, [
            'p' => 'required|string'
        ]);

        $filepath = $request->get('p');

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ]);
        }
        $content = $file->getArchivedFileContent($filepath);
        return response($content);
    }

    public function getAsHtml($id) {
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ]);
        }
        $content = $file->asHtml();
        return response()->json($content);
    }

    // POST

    public function uploadFile(Request $request) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $newFile = File::createFromUpload($file);
        return response()->json($newFile);
    }

    // DELETE

    public function delete($id) {
        try {
            $file = File::findOrFail($id);
            $pathPrefix = 'images/';
            Storage::delete($pathPrefix . $file->name);
            if(isset($file->thumb)) {
                Storage::delete($pathPrefix . $file->thumb);
            }
            $file->delete();
        } catch(ModelNotFoundException $e) {
        }
    }
}
