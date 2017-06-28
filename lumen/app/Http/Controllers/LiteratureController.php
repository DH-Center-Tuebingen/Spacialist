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

    public function getLiterature($id) {
        return response()->json(
            Literature::find($id)
        );
    }

    public function delete($id) {
        DB::table('literature')
            ->where('id', '=', $id)
            ->delete();
    }

    private function getFields($request) {
        $ins = [];

        if(isset($request['type'])) {
            $ins['type'] = $request['type'];
        }

        if(isset($request['title'])) {
            $ins['title'] = $request['title'];
        }

        if(isset($request['author'])) {
            $ins['author'] = $request['author'];
        }

        if(isset($request['editor'])) {
            $ins['editor'] = $request['editor'];
        }

        if(isset($request['journal'])) {
            $ins['journal'] = $request['journal'];
        }

        if(isset($request['year'])) {
            $ins['year'] = $request['year'];
        }

        if(isset($request['pages'])) {
            $ins['pages'] = $request['pages'];
        }

        if(isset($request['volume'])) {
            $ins['volume'] = $request['volume'];
        }

        if(isset($request['number'])) {
            $ins['number'] = $request['number'];
        }

        if(isset($request['booktitle'])) {
            $ins['booktitle'] = $request['booktitle'];
        }

        if(isset($request['publisher'])) {
            $ins['publisher'] = $request['publisher'];
        }

        if(isset($request['address'])) {
            $ins['address'] = $request['address'];
        }

        if(isset($request['annote'])) {
            $ins['annote'] = $request['annote'];
        }

        if(isset($request['chapter'])) {
            $ins['chapter'] = $request['chapter'];
        }

        if(isset($request['crossref'])) {
            $ins['crossref'] = $request['crossref'];
        }

        if(isset($request['edition'])) {
            $ins['edition'] = $request['edition'];
        }

        if(isset($request['institution'])) {
            $ins['institution'] = $request['institution'];
        }

        if(isset($request['key'])) {
            $ins['key'] = $request['key'];
        }

        if(isset($request['month'])) {
            $ins['month'] = $request['month'];
        }

        if(isset($request['note'])) {
            $ins['note'] = $request['note'];
        }

        if(isset($request['organization'])) {
            $ins['organization'] = $request['organization'];
        }

        if(isset($request['school'])) {
            $ins['school'] = $request['school'];
        }

        if(isset($request['series'])) {
            $ins['series'] = $request['series'];
        }

        return $ins;
    }

    public function add(Request $request) {
        $user = \Auth::user();
        if(!$user->can('add_remove_literature')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        if(!$request->has('type') || !$request->has('title')) {
            return response()->json([
                'error' => 'Your literature should have at least a title and a type.'
            ]);
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

    public function edit(Request $request, $id) {
        $user = \Auth::user();
        if(!$user->can('edit_literature')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $literature = Literature::find($id); //TODO findorfail

        $upd = $this->getFields($request->all());
        $upd['lasteditor'] = $user['name'];

        foreach ($upd as $k => $v) {
            $literature->{$k} = $v;
        }

        $literature->save();

        return response()->json([
            'literature' => $literature
        ]);
    }

    public function importBibtex(Request $request) {
        $user = \Auth::user();
        if(!$user->can('add_remove_literature')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        if(!$request->hasFile('file') || !$request->file('file')->isValid()) return response()->json([
            'error' => 'No or invalid file provided'
        ]);
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
            $insArray = $this->getFields($entry);
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

    public function getLiteratures() {
        return response()->json(
            DB::table('literature')
            ->orderBy('author', 'asc')
            ->get()
        );
    }
}
