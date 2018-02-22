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
}
