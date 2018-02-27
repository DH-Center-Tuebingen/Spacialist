<?php

namespace App\Http\Controllers;
use App\Bibliography;
use App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\ParseException;
use RenanBr\BibTexParser\Parser;

class BibliographyController extends Controller
{

    // POST

    public function addItem(Request $request) {
        $this->validate($request, [
            'type' => 'required|alpha'
        ]);

        $user = ['name' => 'Admin']; // TODO \Auth::user();
        $bib = new Bibliography();

        foreach($request->toArray() as $key => $value){
            $bib->{$key} = $value;
        }

        $ckey = Helpers::computeCitationKey($bib->toArray());
        if($ckey === null) {
            return response([
                'error' => 'Could not compute citation key.'
            ]);
        }
        $bib->citekey = $ckey;
        $bib->lasteditor = $user['name'];

        $bib->save();

        return response()->json($bib);
    }

    public function importBibtex(Request $request) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $listener = new Listener();
        $parser = new Parser();
        $parser->addListener($listener);
        try {
            $parser->parseFile($file->getRealPath());
        } catch(ParseException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
        $entries = $listener->export();
        $newEntries = [];
        foreach($entries as $entry) {
            $ckey = $entry['citation-key'];
            $insArray = array_intersect_key($entry, Bibliography::patchRules);
            // set citation key if none is present
            if($ckey == null || $ckey == '') {
                $ckey = Helpers::computeCitationKey($insArray);
            }
            $literature = Bibliography::updateOrCreate(
                ['citekey' => $ckey],
                $insArray
            );
            if($literature->wasRecentlyCreated) {
                $newEntries[] = $literature;
            }
        }
        return response()->json($newEntries);
    }

    public function exportBibtex() {
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
                    case 'lasteditor':
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
}
