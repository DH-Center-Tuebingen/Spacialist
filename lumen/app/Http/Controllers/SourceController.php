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
        $role = 'map_user';
        $user = User::find(1);
        if(!$user->hasRole($role)) {
            return response([
                'error' => 'You are not a member of the role \'' . $role . '\''
            ], 409);
        }
        if(!$user->can('view_concept_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
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
        $role = 'map_user';
        $user = User::find(1);
        if(!$user->hasRole($role)) {
            return response([
                'error' => 'You are not a member of the role \'' . $role . '\''
            ], 409);
        }
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
            $s->literature = DB::table('literature')->where('id', '=', $s->literature_id)->first();
        }
        return response()->json($src);
    }

    public function add(Request $request) {
        $role = 'map_user';
        $user = User::find(1);
        if(!$user->hasRole($role)) {
            return response([
                'error' => 'You are not a member of the role \'' . $role . '\''
            ], 409);
        }
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
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

    public function deleteByLiterature($aid, $cid, $lid) {
        $role = 'map_user';
        $user = User::find(1);
        if(!$user->hasRole($role)) {
            return response([
                'error' => 'You are not a member of the role \'' . $role . '\''
            ], 409);
        }
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        DB::table('sources')
            ->where([
                ['attribute_id', $aid],
                ['context_id', $cid],
                ['literature_id', $lid]
            ])
            ->delete();
    }
}
