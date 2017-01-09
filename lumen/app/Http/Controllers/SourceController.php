<?php

namespace App\Http\Controllers;
use App\User;
use \DB;
use Illuminate\Http\Request;

class SourceController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    public function getByAttribute($aid, $cid) {
        $src = DB::table('sources')
            ->where([
                ['attribute_id', '=', $aid],
                ['context_id', '=', $cid]
            ])
            ->get();
        foreach($src as &$s) {
            $s->literature = DB::table('literature')->where('id', '=', $s->literature_id)->first();
        }
        return response()->json($src);
    }

    public function getByContext($id) {
        $src = DB::table('sources')
                ->select('sources.*', DB::raw("public.\"getLabelForId\"(attributes.thesaurus_id, 'de') AS attribute_name"))
                ->where('context_id', '=', $id)
                ->join('attributes', 'sources.attribute_id', '=', 'attributes.id')
                ->orderBy('attribute_name', 'asc')
                ->get();
        foreach($src as &$s) {
            $s->literature = DB::table('literature')->where('id', '=', $s->literature_id)->first();
        }
        return response()->json($src);
    }

    public function add(Request $request) {
        $user = \Auth::user();
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
        $aid = $request->get('aid');
        $cid = $request->get('cid');
        $lid = $request->get('lid');
        $desc = $request->get('desc');
        $id = DB::table('sources')
            ->insertGetId(
                [
                    'context_id' => $cid,
                    'attribute_id' => $aid,
                    'literature_id' => $lid,
                    'description' => $desc,
                    'lasteditor' => $user['name']
                ]
            );
        return response()->json(['sid' => $id]);
    }

    public function delete($id) {
        DB::table('sources')
            ->where('id', $id)
            ->delete();
    }

    public function deleteByContext($id) {
        DB::table('sources')
            ->where('context_id', $id)
            ->delete();
    }

    public function deleteByAttribute($aid, $cid) {
        DB::table('sources')
            ->where([
                ['attribute_id', $aid],
                ['context_id', $cid]
            ])
            ->delete();
    }

    public function deleteByLiterature($aid, $cid, $lid) {
        DB::table('sources')
            ->where([
                ['attribute_id', $aid],
                ['context_id', $cid],
                ['literature_id', $lid]
            ])
            ->delete();
    }
}
