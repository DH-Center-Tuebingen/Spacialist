<?php

namespace App\Http\Controllers;

use App\File;
use App\Context;
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
        try {
            $file = File::getFileById($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }
        return response()->json($file);
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

    public function getSubFiles(Request $request, $id) {
        try {
            Context::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This entity does not exist'
            ], 400);
        }

        $category = $request->query('c');
        $subFiles = File::getSubFiles($id, $category);

        return response()->json($subFiles);
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

    public function getCategories() {
        return response()->json(File::getCategories());
    }

    public function getCameraNames() {
        $cameras = File::distinct()
            ->orderBy('cameraname', 'asc')
            ->whereNotNull('cameraname')
            ->pluck('cameraname');
        $cameras[] = 'Null';
        return response()->json($cameras);
    }

    public function getDates() {
        $dates = File::distinct()
            ->select(\DB::raw("DATE(created) AS created_date"))
            ->orderBy('created_date', 'asc')
            ->pluck('created_date');
        return response()->json($dates);
    }

    // POST

    public function getFiles(Request $request, $page = 1) {
        $filters = $request->input('filters', []);
        $files = File::getAllPaginate($page, $filters);
        return response()->json($files);
    }

    public function getUnlinkedFiles(Request $request, $page = 1) {
        $filters = $request->input('filters', []);
        $files = File::getUnlinkedPaginate($page, $filters);
        return response()->json($files);
    }

    public function getLinkedFiles(Request $request, $cid, $page = 1) {
        $filters = $request->input('filters', []);
        $files = File::getLinkedPaginate($cid, $page, $filters);
        return response()->json($files);
    }

    public function uploadFile(Request $request) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $newFile = File::createFromUpload($file);
        return response()->json($newFile);
    }

    public function patchContent(Request $request, $id) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }

        $file->setContent($request->file('file'));

        return response()->json($file);
    }

    // PATCH

    public function patchProperty(Request $request, $id) {
        $this->validate($request, [
            'copyright' => 'string',
            'description' => 'string',
            'name' => 'string'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }

        if($request->has('name')) {
            $newName = $request->get('name');
            $otherFileWithName = File::where('name', $newName)->first();
            if(isset($otherFileWithName)) {
                return response()->json([
                    'error' => 'There is already a file with this name'
                ], 400);
            }
        }

        foreach($request->only(['copyright', 'description']) as $key => $value) {
            $file->{$key} = $value;
        }
        $file->save();

        if(isset($newName)) {
            $file->rename($newName);
            $file->setFileInfo();
        }
        return response()->json($file);
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
