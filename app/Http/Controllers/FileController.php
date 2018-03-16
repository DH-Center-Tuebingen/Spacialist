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
            ], 400);
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
            ], 400);
        }
        $content = $file->asHtml();
        return response()->json($content);
    }

    public function getLinkCount($id) {
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }
        return response()->json($file->linkCount());
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

    // PUT

    public function linkToEntity(Request $request, $id) {
        $this->validate($request, [
            'context_id' => 'required|integer|exists:contexts,id'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }

        $file->link($request->get('context_id'));

        return response()->json();
    }

    // DELETE

    public function deleteFile($id) {
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }

        $file->deleteFile();

        return response()->json(null, 204);
    }

    public function unlinkEntity($fid, $cid) {

        try {
            $file = File::findOrFail($fid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }

        try {
            Context::findOrFail($cid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This entity does not exist'
            ], 400);
        }

        $file->unlink($cid);

        return response()->json(null, 204);
    }
}
