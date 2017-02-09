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

        if($request->has('address')) {
            $ins['address'] = $request->get('address');
        }

        return $ins;
    }

    public function add(Request $request) {
        if(!$request->has('type') || !$request->has('title')) {
            return response()->json([
                'error' => 'Your literature should have at least a title and a type.'
            ]);
        }

        $ins = $this->getFields($request);

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
        if(!$request->has('id')) {
            return response()->json([
                'error' => 'No ID given.'
            ]);
        }

        $id = $request->get('id');

        $upd = $this->getFields($request);

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
