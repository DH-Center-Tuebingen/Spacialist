<?php

namespace App\Http\Controllers;
use App\User;
use App\Literature;
use App\Source;
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

    // GET

    public function getByContext($id) {
        $user = \Auth::user();
        if(!$user->can('view_concept_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $src = DB::table('sources')
                ->select('sources.*', DB::raw("(select label from getconceptlabelsfromurl where concept_url = attributes.thesaurus_url and short_name = 'de' limit 1) AS attribute_name"))
                ->where('context_id', '=', $id)
                ->join('attributes', 'sources.attribute_id', '=', 'attributes.id')
                ->orderBy('attribute_name', 'asc')
                ->get();
        foreach($src as &$s) {
            $s->literature = Literature::find($s->literature_id);
        }
        return response()->json([
            'sources' => $src
        ]);
    }

    // POST

    public function add(Request $request) {
        $this->validate($request, [
            'aid' => 'required|integer',
            'cid' => 'required|integer',
            'lid' => 'required|integer',
            'desc' => 'required|string',
        ]);

        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $user = \Auth::user();
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
        return response()->json([
            'source' => $this->getById($id)
        ]);
    }

    // PATCH

    // PUT

    // DELETE

    public function delete($id) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        Source::find($id)->delete();
    }

    // OTHER FUNCTIONS

    private function getById($id) {
        $src = DB::table('sources as s')
                ->select('s.*', DB::raw("(select label from getconceptlabelsfromurl where concept_url = a.thesaurus_url and short_name = 'de' limit 1) AS attribute_name"))
                ->where('s.id', '=', $id)
                ->join('attributes as a', 's.attribute_id', '=', 'a.id')
                ->orderBy('attribute_name', 'asc')
                ->first();
        $src->literature = Literature::find($src->literature_id);
        return $src;
    }
}
