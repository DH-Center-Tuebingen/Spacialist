<?php

namespace App\Http\Controllers;
use App\User;
use App\Literature;
use App\Source;
use App\Attribute;
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
        $src = Source::where('context_id', '=', $id)->get();
        foreach($src as &$s) {
            $s->literature = Literature::find($s->literature_id);
            $s->attribute_url = Attribute::where('id', $s->attribute_id)->value('thesaurus_url');
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
        $src = new Source();
        $src->context_id = $cid;
        $src->attribute_id = $aid;
        $src->literature_id = $lid;
        $src->description = $desc;
        $src->lasteditor = $user['name'];
        $src->save();
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
