<?php

namespace App\Http\Controllers;
use App\Bibliography;
use App\File\DownloadHandler;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Exception\ParserException;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor\TagNameCaseProcessor;

class BibliographyController extends Controller
{
    private static $folder = 'bibliography' . DIRECTORY_SEPARATOR;

    // GET
    public function getBibliographyItem(int $id) {
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

        return response()->json($bib);
    }

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

    public function downloadFile(Request $request) {
        $user = auth()->user();
        if(!$user->can('bibliography_read')) {
            return response()->json([
                'error' => __('You do not have the permission to view attached files')
            ], 403);
        }

        $filepath = $request->query('path');
        return Bibliography::getFileDirectory()->download($filepath);
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
            'entry_type' => 'required|alpha|bibtex_type',
            'file' => 'file',
        ]);

        $bib = new Bibliography();
        $success = $bib->fieldsFromRequest($request->except('file'), $user);
        if(!$success) {
            return response()->json([
                'error' => __('At least one required field is not set')
            ], 422);
        }

        $file = $request->file('file');
        try{
            if($file) $bib->uploadFile($file);
        }catch(\Exception $e){
            info($e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
        return response()->json($bib->refresh(), 201);
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

        // TODO:: This is too much logic for a controller. This should be moved inside the Model!

        $file = $request->file('file');
        $noOverwriteOnDup = $request->input('no-overwrite', false);
        $listener = new Listener();
        $listener->addProcessor(new TagNameCaseProcessor(CASE_LOWER));
        $listener->addProcessor(function($entry) {
            $entry['entry_type'] = strtolower($entry['_type']);
            return $entry;
        });
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
        $errored = null;

        DB::beginTransaction();

        foreach($entries as $entry) {
            // Test if type exists(?)
            $entry_type = $entry['entry_type'];
            $isValid = Bibliography::validateMandatory($entry, $entry_type);
            if(!$isValid) {
                $errored = [
                    'type' => 'validation',
                    'on' => $entry['_original'],
                ];
                break;
            }

            $insArray = Bibliography::stripDisallowed($entry, $entry_type);
            // unset file, because file upload is (currently) not possible in import
            $insArray['file'] = null;

            // set citation key if none is present
            $useKeyInDupCheck = false;
            if(!array_key_exists('citation-key', $entry) || $entry['citation-key'] == '') {
                $ckey = Bibliography::computeCitationKey($insArray);
            } else {
                $ckey = $entry['citation-key'];
                // if key is provided by uploaded file, use it to determine if entry already exists in db
                $useKeyInDupCheck = true;
            }
            $insArray['citekey'] = $ckey;
            $insArray['user_id'] = $user->id;

            $duplicate = Bibliography::duplicateCheck($insArray, $useKeyInDupCheck);
            // if it is not a duplicate, create a new entry
            if($duplicate === false) {
                $bibliography = Bibliography::create($insArray);
                $newChangedEntries[] = [
                    'entry' => Bibliography::find($bibliography->id),
                    'added' => true,
                ];
            } else {
                if($noOverwriteOnDup) {
                    $errored = [
                        'type' => 'duplicate',
                        'on' => $entry['_original'],
                    ];
                    break;
                } else {
                    foreach($insArray as $key => $value) {
                        $duplicate->{$key} = $value;
                    }
                    $duplicate->save();
                    $newChangedEntries[] = [
                        'entry' => Bibliography::find($duplicate->id),
                        'added' => false,
                    ];
                }
            }
        }

        if(isset($errored)) {
            DB::rollBack();
            if($errored['type'] == 'duplicate') {
                $msg = 'Overwrite parameter not set! Existing entry is matching ' . $errored['on'];
            } else if($errored['type'] == 'validation') {
                $msg = 'Validation failed for ' . $errored['on'];
            }
            return response()->json([
                'error' => $msg,
            ], 400);
        }

        DB::commit();

        return response()->json($newChangedEntries, 201);
    }

    public function exportBibtex(Request $request) {
        $user = auth()->user();
        if(!$user->can('bibliography_share')) {
            return response()->json([
                'error' => __('You do not have the permission to export bibliography')
            ], 403);
        }

        $this->validate($request, [
            'selection' => 'nullable|array',
        ]);

        $query = Bibliography::orderBy('author', 'asc');

        $selection = $request->get('selection', []);
        if(isset($selection) && !empty($selection)) {
            $query = $query->whereIn('id', $selection);
        }

        $entries = $query->get();
        $content = '';

        foreach($entries as $e) {
            $content .= "@{$e->entry_type}{{$e->citekey},\n";
            $attrs = $e->getAttributes();
            $added = false;
            for($i = 0; $i < count($attrs); $i++) {
                $key = array_keys($attrs)[$i];
                $value = array_values($attrs)[$i];
                if(!isset($value)) continue;
                switch($key) {
                    case 'id':
                    case 'entry_type':
                    case 'created_at':
                    case 'updated_at':
                    case 'citekey':
                    case 'user_id':
                        break;
                    default:
                        $added = true;
                        $content .= "    $key = {{$value}},\n";
                        break;
                }
            }
            $content = ($added)? substr($content, 0, -2) : $content;
            $content .= "\n}";
            $content .= "\n\n";
        }

        // remove new lines at end of file
        $content = substr($content, 0, -2);

        return response()->streamDownload(function() use ($content) {
            echo $content;
        }, 'export.bib', [
            'Content-Type' => 'application/x-bibtex'
        ]);
    }

    public function updateItem(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('bibliography_write')) {
            return response()->json([
                'error' => __('You do not have the permission to edit existing bibliography')
            ], 403);
        }
        $this->validate($request, [
            'entry_type' => 'alpha|bibtex_type',
            'file' => 'file',
            'delete_file' => 'boolean_string',
        ]);

        try {
            $bib = Bibliography::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This bibliography item does not exist')
            ], 400);
        }

        $success = $bib->fieldsFromRequest($request->except(['file', 'delete_file']), $user);
        if(!$success) {
            return response()->json([
                'error' => __('At least one required field is not set')
            ], 422);
        }

        $file = $request->file('file');
        if(isset($file)) {
            $bib->file = $bib->uploadFile($file);
        } else if($request->has('delete_file') && sp_parse_boolean($request->get('delete_file'))) {
            $bib->deleteFile();
        }
        return response()->json($bib->refresh());
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

        // Only delete file from storage to not fire 'save' event on item
        $bib->deleteFileFromStorage();
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

        $item->deleteFile();
        return response()->json(null, 204);
    }
}
