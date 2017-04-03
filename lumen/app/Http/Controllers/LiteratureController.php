<?php

namespace App\Http\Controllers;
use App\User;
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

    public function getById($id) {
        return response()->json(
            DB::table('literature')
                ->where('id', '=', $id)
                ->get()
        );
    }

    public function delete($id) {
        DB::table('literature')
            ->where('id', '=', $id)
            ->delete();
    }

    private function getFields(Request $request) {
        $ins = [];

        if($request->has('type')) {
            $ins['type'] = $request->get('type');
        }

        if($request->has('title')) {
            $ins['title'] = $request->get('title');
        }

        if($request->has('author')) {
            $ins['author'] = $request->get('author');
        }

        if($request->has('editor')) {
            $ins['editor'] = $request->get('editor');
        }

        if($request->has('journal')) {
            $ins['journal'] = $request->get('journal');
        }

        if($request->has('year')) {
            $ins['year'] = $request->get('year');
        }

        if($request->has('pages')) {
            $ins['pages'] = $request->get('pages');
        }

        if($request->has('volume')) {
            $ins['volume'] = $request->get('volume');
        }

        if($request->has('number')) {
            $ins['number'] = $request->get('number');
        }

        if($request->has('booktitle')) {
            $ins['booktitle'] = $request->get('booktitle');
        }

        if($request->has('publisher')) {
            $ins['publisher'] = $request->get('publisher');
        }

        if($request->has('annote')) {
            $ins['annote'] = $request->get('annote');
        }

        if($request->has('chapter')) {
            $ins['chapter'] = $request->get('chapter');
        }

        if($request->has('crossref')) {
            $ins['crossref'] = $request->get('crossref');
        }

        if($request->has('edition')) {
            $ins['edition'] = $request->get('edition');
        }

        if($request->has('institution')) {
            $ins['institution'] = $request->get('institution');
        }

        if($request->has('key')) {
            $ins['key'] = $request->get('key');
        }

        if($request->has('month')) {
            $ins['month'] = $request->get('month');
        }

        if($request->has('note')) {
            $ins['note'] = $request->get('note');
        }

        if($request->has('organization')) {
            $ins['organization'] = $request->get('organization');
        }

        if($request->has('school')) {
            $ins['school'] = $request->get('school');
        }

        if($request->has('series')) {
            $ins['series'] = $request->get('series');
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

        $ins = $this->getFields($request);
        $ins['lasteditor'] = $user['name'];

        if($request->has('id')) {
            $id = $request->get('id');
            DB::table('literature')
                ->where('id', '=', $id)
                ->update($ins);
        } else {
            $id = DB::table('literature')
                ->insertGetId($ins);
        }

        $lit = DB::table('literature')
            ->where('id', '=', $id)
            ->get();
        return response()->json([
            'literature' => $lit
        ]);
    }

    public function edit(Request $request) {
        $user = \Auth::user();
        if(!$user->can('edit_literature')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        if(!$request->has('id')) {
            return response()->json([
                'error' => 'No ID given.'
            ]);
        }

        $id = $request->get('id');

        $upd = $this->getFields($request);
        $upd = $user['name'];

        DB::table('literature')
            ->where('id', '=', $id)
            ->update($upd);
    }

    public function getAll() {
        return response()->json(
            DB::table('literature')
            ->orderBy('author', 'asc')
            ->get()
        );
    }
}
