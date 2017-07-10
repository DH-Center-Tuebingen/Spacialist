<?php

namespace App\Http\Controllers;
use App\User;
use App\Literature;
use \DB;
use Illuminate\Http\Request;

class LiteratureController extends Controller
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

    public function getLiteratures() {
        return response()->json(
            DB::table('literature')
            ->orderBy('author', 'asc')
            ->get()
        );
    }

    public function getLiterature($id) {
        try {
            $entry = Literature::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $entry = [
                'error' => 'This literature entry does not exist'
            ];
        }
        return response()->json($entry);
    }

    // POST

    public function add(Request $request) {
        $this->validate($request, [
            'type' => 'required|alpha',
            'title' => 'required|string'
        ]);

        $user = \Auth::user();
        if(!$user->can('add_remove_literature')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $literature = new Literature();

        foreach($request->toArray() as $key => $value){
            $literature->{$key} = $value;
        }

        $literature->lasteditor = $user['name'];

        $literature->save();


        return response()->json([
            'literature' => $literature
        ]);
    }

    public function importBibtex(Request $request) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $user = \Auth::user();
        if(!$user->can('add_remove_literature')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }

        $file = $request->file('file');
        $listener = new \RenanBr\BibTexParser\Listener;
        $parser = new \RenanBr\BibTexParser\Parser;
        $parser->addListener($listener);
        try {
            $parser->parseFile($file->getRealPath());
        } catch(\RenanBr\BibTexParser\ParseException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
        $entries = $listener->export();
        $newEntries = [];
        foreach($entries as $entry) {
            $insArray = array_intersect_key($entry, Literature::patchRules);
            if(Literature::where($insArray)->first() === null) {
                $literature = new Literature($insArray);
                $literature->save();
                $newEntries[] = $literature;
            }
        }
        return response()->json([
            'entries' => $newEntries
        ]);
    }

    // PATCH

    public function edit(Request $request, $id) {
        $user = \Auth::user();
        if(!$user->can('edit_literature')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $this->validate($request, Literature::patchRules);

        $literature = Literature::find($id); //TODO findorfail

        $literature->lasteditor = $user['name'];

        foreach ($request->intersect(array_keys(Literature::patchRules)) as $key => $value) {
            $literature->{$key} = $value;
        }

        $literature->save();

        return response()->json([
            'literature' => $literature
        ]);
    }

    // PUT

    // DELETE

    public function delete($id) {
        Literature::find($id)->delete();
    }
}
