<?php

namespace App\Http\Controllers;

use App\File;
use App\Context;
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
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to view a specific file'
            ], 403);
        }
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
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to view a specific file'
            ], 403);
        }
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
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to download parts of a zip file'
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
                'error' => 'This file does not exist'
            ], 400);
        }
        $content = $file->getArchivedFileContent($filepath);
        return response($content);
    }

    public function getAsHtml($id) {
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to view a specific file as HTML'
            ], 403);
        }
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
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to view successors of a specific file'
            ], 403);
        }
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
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to get the file categories'
            ], 403);
        }
        if(auth()->check()) {
            $locale = Preference::getUserPreference($user['id'], 'prefs.gui-language')->value;
        } else {
            $locale = \App::getLocale();
        }
        return response()->json(File::getCategories($locale));
    }

    public function getCameraNames() {
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to get the camera names'
            ], 403);
        }
        $cameras = File::distinct()
            ->orderBy('cameraname', 'asc')
            ->whereNotNull('cameraname')
            ->pluck('cameraname');
        $cameras[] = 'Null';
        return response()->json($cameras);
    }

    public function getDates() {
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to get the file dates'
            ], 403);
        }
        $dates = File::distinct()
            ->select(\DB::raw("DATE(created) AS created_date"))
            ->orderBy('created_date', 'asc')
            ->pluck('created_date');
        $years = File::distinct()
            ->select(\DB::raw("EXTRACT(year from created) AS created_year"))
            ->orderBy('created_year', 'asc')
            ->pluck('created_year');
        $dates = $dates->map(function($d) {
            return [
                'is' => 'date',
                'value' => $d
            ];
        });
        $years = $years->map(function($y) {
            return [
                'is' => 'year',
                'value' => $y
            ];
        });
        $res = $dates->concat($years);
        return response()->json($res);
    }

    public function getTags() {
        $tagObj = Preference::where('label', 'prefs.tag-root')
            ->value('default_value');
        $tagUri = json_decode($tagObj)->uri;
        $tags = \DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.narrower_id as id, c2.concept_url
                FROM th_broaders br
                JOIN th_concept c ON c.id = br.broader_id
                JOIN th_concept c2 ON c2.id = br.narrower_id
                WHERE c.concept_url = '$tagUri'
                UNION
                SELECT br.narrower_id as id, c.concept_url
                FROM top t, th_broaders br
                JOIN th_concept c ON c.id = br.narrower_id
                WHERE t.id = br.broader_id
            )
            SELECT *
            FROM top
        ");
        return response()->json($tags);
    }

    // POST

    public function getFiles(Request $request, $page = 1) {
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to view files'
            ], 403);
        }
        $filters = $request->input('filters', []);
        $files = File::getAllPaginate($page, $filters);
        return response()->json($files);
    }

    public function getUnlinkedFiles(Request $request, $page = 1) {
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to view files'
            ], 403);
        }
        $filters = $request->input('filters', []);
        $files = File::getUnlinkedPaginate($page, $filters);
        return response()->json($files);
    }

    public function getLinkedFiles(Request $request, $cid, $page = 1) {
        $user = auth()->user();
        if(!$user->can('view_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to view files'
            ], 403);
        }
        $filters = $request->input('filters', []);
        $files = File::getLinkedPaginate($cid, $page, $filters);
        return response()->json($files);
    }

    public function uploadFile(Request $request) {
        $user = auth()->user();
        if(!$user->can('manage_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to upload files'
            ], 403);
        }
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $newFile = File::createFromUpload($file, $user);
        return response()->json($newFile);
    }

    public function patchContent(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('manage_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to edit a file\'s content'
            ], 403);
        }
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
        $file->setFileInfo();

        return response()->json($file);
    }

    // PATCH

    public function patchProperty(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('manage_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to modify file properties'
            ], 403);
        }
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

    public function patchTags(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('manage_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to modify file properties'
            ], 403);
        }
        $this->validate($request, [
            'tags' => 'array'
        ]);

        try {
            $file = File::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'This file does not exist'
            ], 400);
        }

        $tags = $request->input('tags', []);
        $tags = array_map(function($t) {
            return $t['id'];
        }, $tags);

        // Delete all entries where tags no longer set
        FileTag::where('photo_id', $file->id)
            ->whereNotIn('concept_id', $tags)
            ->delete();

        // Get current tags...
        $currentTags = FileTag::where('photo_id', $file->id)
            ->pluck('concept_id')->toArray();

        // ... and remove them from requested tags...
        $newTags = array_diff($tags, $currentTags);

        // ... so we can set all new tags
        foreach($newTags as $t) {
            $pt = new FileTag();
            $pt->photo_id = $file->id;
            $pt->concept_id = $t;
            $pt->save();
        }

        return response()->json(null, 204);
    }

    // PUT

    public function linkToEntity(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('link_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to link files'
            ], 403);
        }
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

        $file->link($request->get('context_id'), $user);

        return response()->json();
    }

    // DELETE

    public function deleteFile($id) {
        $user = auth()->user();
        if(!$user->can('manage_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to delete files'
            ], 403);
        }
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
        $user = auth()->user();
        if(!$user->can('link_photos')) {
            return response()->json([
                'error' => 'You do not have the permission to unlink files'
            ], 403);
        }

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
