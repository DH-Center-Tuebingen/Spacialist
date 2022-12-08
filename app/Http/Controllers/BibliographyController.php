<?php

namespace App\Http\Controllers;
use App\Bibliography;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Exception\ParserException;
use RenanBr\BibTexParser\Parser;

class BibliographyController extends Controller
{

    // GET
    public function getBibliography() {
        $user = auth()->user();
        if(!$user->can('bibliography_read')) {
            return response()->json([
                'error' => __('You do not have the permission to read bibliography')
            ], 403);
        }

        $bibliography = Bibliography::orderBy('id')->get();

        return response()->json($bibliography);
    }

    public function exportBibtex() {
        $user = auth()->user();
        if(!$user->can('bibliography_share')) {
            return response()->json([
                'error' => __('You do not have the permission to export bibliography')
            ], 403);
        }

        $entries = Bibliography::orderBy('author', 'asc')->get();
        $content = '';
        foreach($entries as $e) {
            $content .= '@'.$e->type.'{'.$e->citekey.',';
            $content .= "\n";
            $attrs = $e->getAttributes();
            foreach($attrs as $k => $a) {
                if(!isset($a)) continue;
                switch($k) {
                    case 'id':
                    case 'type':
                    case 'created_at':
                    case 'updated_at':
                    case 'citekey':
                    case 'user_id':
                        break;
                    default:
                        $content .= '    '.$k.': {'.$a.'}';
                        $content .= "\n";
                        break;
                }
            }
            $content .= '}';
            $content .= "\n\n";
        }
        return response()->streamDownload(function() use ($content) {
            echo $content;
        }, 'export.bib', [
            'Content-Type' => 'application/x-bibtex'
        ]);
    }

    public function getReferenceCount($id) {
        $user = auth()->user();
        if(!$user->can('bibliography_read')) {
            return response()->json([
                'error' => __('You do not have the permission to read bibliography')
            ], 403);
        }

        try {
            $bib = Bibliography::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This bibliography item does not exist')
            ], 400);
        }
        $count = $bib->referenceCount();
        return response()->json($count);
    }

    // POST

    public function addItem(Request $request) {
        $user = auth()->user();
        if(!$user->can('bibliography_create')) {
            return response()->json([
                'error' => __('You do not have the permission to add new bibliography')
            ], 403);
        }
        $this->validate($request, [
            'type' => 'required|alpha',
            'title' => 'required|string',
            'file' => 'file',
        ]);

        $file = $request->file('file');
        $bib = new Bibliography();
        $success = $bib->fieldsFromRequest($request->except('file'), $user);
        if(!$success) {
            return response()->json([
                'error' => __('At least one required field is not set')
            ], 422);
        }

        if(isset($file)) {
            $bib->file = $bib->uploadFile($file);
            $bib->user_id = $user->id;
            $bib->save();
        }
        $bib = Bibliography::find($bib->id);

        return response()->json($bib, 201);
    }

    public function importBibtex(Request $request) {
        $user = auth()->user();
        if(!$user->can('bibliography_create') || !$user->can('bibliography_write')) {
            return response()->json([
                'error' => __('You do not have the permission to add new/modify existing bibliography items')
            ], 403);
        }
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $listener = new Listener();
        $parser = new Parser();
        $parser->addListener($listener);
        try {
            $parser->parseFile($file->getRealPath());
        } catch(ParserException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
        $entries = $listener->export();
        $newChangedEntries = [];
        foreach($entries as $entry) {
            $insArray = array_intersect_key($entry, Bibliography::patchRules);
            // set citation key if none is present
            if(!array_key_exists('citation-key', $entry) || $entry['citation-key'] == '') {
                $ckey = Bibliography::computeCitationKey($insArray);
            } else {
                $ckey = $entry['citation-key'];
            }
            $insArray['user_id'] = $user->id;
            $bibliography = Bibliography::updateOrCreate(
                ['citekey' => $ckey],
                $insArray
            );
            if($bibliography->wasRecentlyCreated) {
                $newChangedEntries[] = [
                    'entry' => Bibliography::find($bibliography->id),
                    'added' => true,
                ];
            } else if($bibliography->wasChanged()) {
                $newChangedEntries[] = [
                    'entry' => Bibliography::find($bibliography->id),
                    'added' => false,
                ];
            }
        }
        return response()->json($newChangedEntries, 201);
    }

    // PATCH

    public function updateItem(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('bibliography_write')) {
            return response()->json([
                'error' => __('You do not have the permission to edit existing bibliography')
            ], 403);
        }
        $this->validate($request, [
            'type' => 'alpha|bibtex_type',
            'file' => 'file',
        ]);

        try {
            $bib = Bibliography::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This bibliography item does not exist')
            ], 400);
        }
        $file = $request->file('file');

        $success = $bib->fieldsFromRequest($request->except('file'), $user);
        if(!$success) {
            return response()->json([
                'error' => __('At least one required field is not set')
            ], 422);
        }
        if(isset($file)) {
            $bib->file = $bib->uploadFile($file);
            $bib->user_id = $user->id;
            $bib->save();
        }
        $bib = Bibliography::find($bib->id);

        return response()->json($bib);
    }

    // DELETE

    public function deleteItem($id) {
        $user = auth()->user();
        if(!$user->can('bibliography_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to remove bibliography entries')
            ], 403);
        }
        try {
            $bib = Bibliography::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This bibliography item does not exist')
            ], 400);
        }

        $bib->delete();

        return response()->json(null, 204);
    }

    public function deleteItemFile($id) {
        $user = auth()->user();
        if(!$user->can('bibliography_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to remove bibliography entries')
            ], 403);
        }

        try {
            $item = Bibliography::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This bibliography item does not exist')
            ], 400);
        }

        Storage::delete($item->file);
        $item->file = null;
        $item->save();
        return response()->json(null, 204);
    }
}
