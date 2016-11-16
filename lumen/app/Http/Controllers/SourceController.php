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

    public function getByAttribute($aid, $fid) {
        $src = DB::table('sources')
            ->where([
                ['attribute_id', '=', $aid],
                ['find_id', '=', $fid]
            ])
            ->get();
        foreach($src as &$s) {
            $s->literature = DB::table('bib_tex')->where('id', '=', $s->literature_id)->first();
        }
        return response()->json($src);
    }

    public function getByContext($id) {
        $src = DB::table('sources')
                ->select('sources.*', DB::raw("public.\"getLabelForId\"(attributes.thesaurus_id, 'de') AS attribute_name"))
                ->where('find_id', '=', $id)
                ->join('attributes', 'sources.attribute_id', '=', 'attributes.id')
                ->orderBy('attribute_name', 'asc')
                ->get();
        foreach($src as &$s) {
            $s->literature = DB::table('bib_tex')->where('id', '=', $s->literature_id)->first();
        }
        return response()->json($src);
    }

    public function add(Request $request) {
        $aid = $request->get('aid');
        $fid = $request->get('fid');
        $lid = $request->get('lid');
        $desc = $request->get('desc');
        $id = DB::table('sources')
            ->insertGetId(
                [
                    'find_id' => $fid,
                    'attribute_id' => $aid,
                    'literature_id' => $lid,
                    'description' => $desc
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
            ->where('find_id', $id)
            ->delete();
    }

    public function deleteByAttribute($aid, $fid) {
        DB::table('sources')
            ->where([
                ['attribute_id', $aid],
                ['find_id', $fid]
            ])
            ->delete();
    }

    public function deleteByLiterature($aid, $fid, $lid) {
        DB::table('sources')
            ->where([
                ['attribute_id', $aid],
                ['find_id', $fid],
                ['literature_id', $lid]
            ])
            ->delete();
    }
}
