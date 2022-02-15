<?php

namespace App\Plugins\File\Controllers;

use App\Entity;
use App\FileTag;
use App\Http\Controllers\Controller;
use App\Plugins\File\App\File;
use App\Preference;
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

    public function asHtml($id) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view a specific file as HTML')
            ], 403);
        }
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }
        return response()->json($file->asHtml());
    }

    public function getArchiveFileList($id) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view a specific file')
            ], 403);
        }
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }
        $content = $file->getArchiveFileList();
        return response()->json($content);

    }

    public function downloadArchivedFile(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to download parts of a zip file')
            ], 403);
        }
        $this->validate($request, [
            'p' => 'required|string'
        ]);

        $filepath = $request->get('p');

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }
        $fileInfo = $file->getArchivedFileContent($filepath);
        info($fileInfo);
        return response($fileInfo['content'])->header('Content-Type', $fileInfo['mime_type']);
    }

    public function getFilterOptions(Request $request) {
        $user = auth()->user();

        $type = $request->get('t');

        if(!$user->can('view_files')) {
            switch($type) {
                case 'category':
                    $msg = __('You do not have the permission to get the file categories');
                    break;
                case 'camera':
                    $msg = __('You do not have the permission to get the camera names');
                    break;
                case 'date':
                    $msg = __('You do not have the permission to get the file dates');
                    break;
                default:
                    $msg = __('You do not have the permission to view files');
                    break;
            }
            return response()->json([
                'error' => $msg
            ], 403);
        }
        if(auth()->check()) {
            $locale = $user->getLanguage();
        } else {
            $locale = \App::getLocale();
        }

        switch($type) {
            case 'category':
                $data = File::getCategories($locale);
                break;
            case 'camera':
                $data = File::getCamerasUsed();
                break;
            case 'date':
                $data = File::getDatesCreated('full');
                break;
            default:
                $data = [
                    'category' => File::getCategories($locale),
                    'camera' => File::getCamerasUsed(),
                    'date' => File::getDatesCreated('full'),
                ];
                break;
        }

        return response()->json($data);
    }

    // POST

    public function getFiles(Request $request, $page = 1) {
        $user = auth()->user();
        if (!$user->can('view_files')) {
            return response()->json([
                'error' => __('You do not have the permission to view files')
            ], 403);
        }
        $filters = $request->input('filters', []);
        $entity = $request->query('id', null);
        $type = $request->query('t', 'unlinked');
        $skip = $request->query('skip', 0);
        $files = File::filter($page, $filters, $type, $entity, $skip);
        return response()->json($files);
    }

    public function replaceFileContent(Request $request, $id) {
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

        return response()->json($file, 201);
    }

    public function uploadFile(Request $request) {
        $user = auth()->user();
        if(!$user->can('manage_files')) {
            return response()->json([
                'error' => __('You do not have the permission to upload files')
            ], 403);
        }
        $this->validate($request, [
            'file' => 'required|file',
            'copyright' => 'string',
            'description' => 'string',
            'tags' => 'json',
        ]);

        $file = $request->file('file');
        $metadata = [
            'copyright' => $request->get('copyright'),
            'description' => $request->get('description'),
            'tags' => json_decode($request->get('tags'))
        ];

        $newFile = File::createFromUpload($file, $user, $metadata);
        $newFile->loadMissing('entities');
        $newFile->loadMissing('tags');
        $newFile->setFileInfo();

        return response()->json($newFile, 201);
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
        [
            'path' => $filepath,
            'type' => $mime,
        ] = File::createArchiveFromList($files);
        // get raw parsed content
        $content = file_get_contents($filepath);
        // delete tmp file
        // unlink($filepath);
        // return response()->streamDownload(function() use ($content) {
        //     echo $content;
        // }, 'export.zip', [
        //     'Content-Type' => $mime
        // ]);
        return response(base64_encode($content))->header('Content-Type', $mime);
    }

    public function deleteFilesBatch(Request $request) {
        $user = auth()->user();
        if(!$user->can('manage_files')) {
            return response()->json([
                'error' => __('You do not have the permission to delete files')
            ], 403);
        }
        $this->validate($request, [
            'files' => 'required|array'
        ]);

        $files = $request->input('files', []);

        foreach($files as $fileid) {
            try {
                $file = File::findOrFail($fileid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This file does not exist')
                ], 400);
            }
    
            $file->deleteFile();
        }

        return response()->json(null, 204);
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

    public function patchTags(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('manage_files')) {
            return response()->json([
                'error' => __('You do not have the permission to modify file properties')
            ], 403);
        }
        $this->validate($request, [
            'tags' => 'array'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }

        $tags = $request->input('tags', []);

        // Delete all entries where tags no longer set
        FileTag::where('file_id', $file->id)
            ->whereNotIn('concept_id', $tags)
            ->delete();

        // Get current tags...
        $currentTags = FileTag::where('file_id', $file->id)
            ->pluck('concept_id')->toArray();

        // ... and remove them from requested tags...
        $newTags = array_diff($tags, $currentTags);

        // ... so we can set all new tags
        foreach($newTags as $t) {
            $pt = new FileTag();
            $pt->file_id = $file->id;
            $pt->concept_id = $t;
            $pt->save();
        }

        return response()->json(null, 204);
    }

    // PUT

    public function linkToEntity(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('link_files')) {
            return response()->json([
                'error' => __('You do not have the permission to link files')
            ], 403);
        }
        $this->validate($request, [
            'entity_id' => 'required|integer|exists:entities,id'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }

        $file->link($request->get('entity_id'), $user);

        return response()->json(null, 204);
    }

    // DELETE

    public function deleteFile($id) {
        $user = auth()->user();
        if(!$user->can('manage_files')) {
            return response()->json([
                'error' => __('You do not have the permission to delete files')
            ], 403);
        }
        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }

        $file->deleteFile();

        return response()->json(null, 204);
    }

    public function unlinkEntity($id, $eid) {
        $user = auth()->user();
        if(!$user->can('link_files')) {
            return response()->json([
                'error' => __('You do not have the permission to unlink files')
            ], 403);
        }

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This file does not exist')
            ], 400);
        }

        try {
            Entity::findOrFail($eid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $file->unlink($eid);

        return response()->json(null, 204);
    }
}
