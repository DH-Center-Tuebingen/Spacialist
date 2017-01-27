<?php

namespace App\Http\Controllers;
use Log;
use App\User;
use App\Permission;
use App\Role;
use Zizaco\Entrust;
use \DB;
use Illuminate\Http\Request;

class ContextController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    private function getData($id) {
        $data = DB::table('attribute_values as av')->select('av.*', 'a.datatype', 'a.thesaurus_root_url')->join('attributes as a', 'av.attribute_id', '=', 'a.id')->where('context_id', $id)->get();
        foreach($data as &$attr) {
            if($attr->datatype == 'literature') {
                $attr->literature_info = DB::table('literature')->where('id', $attr->str_val)->first();
            } else if($attr->datatype == 'string-sc' || $attr->datatype == 'string-mc') {
                $attr->val = DB::table('th_concept')
                    ->select('id as narrower_id',
                        DB::raw("'".DB::table('getconceptlabelsfromurl')
                        ->where('concept_url', $attr->thesaurus_val)
                        ->where('short_name', 'de')
                        ->value('label')."' as narr")
                    )
                    ->where('concept_url', '=', $attr->thesaurus_val)
                    ->first();
            } else if($attr->datatype == 'dimension') {
                $elems = explode(';', $attr->str_val, 4);
                if(count($elems) != 4) continue;
                $attr->val = json_encode(['B' => floatval($elems[0]), 'H' => floatval($elems[1]), 'T' => floatval($elems[2]), 'unit' => $elems[3]]);
            } else if($attr->datatype == 'epoch') {
                $elems = explode(';', $attr->str_val, 3);
                if(count($elems) != 3) continue;
                if($elems[0] != '') {
                    $start = intval($elems[0]);
                    $startLabel = $start < 0 ? 'v. Chr.' : 'n. Chr.';
                    $start = abs($start);
                } else {
                    $start = '';
                    $startLabel = '';
                }
                if($elems[1] != '') {
                    $end = intval($elems[1]);
                    $endLabel = $end < 0 ? 'v. Chr.' : 'n. Chr.';
                    $end = abs($end);
                } else {
                    $end = '';
                    $endLabel = '';
                }
                $thUri = $elems[2];
                $attr->val = json_encode([
                    'startLabel' => $startLabel,
                    'start' => $start,
                    'endLabel' => $endLabel,
                    'end' => $end,
                    'epoch' => DB::table('th_concept')
                                ->select('id as narrower_id',
                                    DB::raw("'".DB::table('getconceptlabelsfromurl')
                                    ->where('concept_url', $thUri)
                                    ->where('short_name', 'de')
                                    ->value('label')."' as narr")
                                )
                                ->where('concept_url', '=', $thUri)
                                ->first()
                ]);
            }
        }
        return $data;
    }

    public function get() {
        return response()->json(
            DB::table('context_types as c')
                ->select('c.thesaurus_url as index', 'ca.context_type_id as ctid', 'ca.attribute_id as aid', 'a.datatype', 'c.type',
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = c.thesaurus_url and short_name = 'de' limit 1) as title"),
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = a.thesaurus_url and short_name = 'de' limit 1) as val")
                )
                ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
                ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->where('c.type', '=', '0')
                ->orderBy('val')
                ->get()
        );
    }

    public function getRecursive() {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $rootFields = DB::select("
        WITH RECURSIVE
        q AS (
	        SELECT  c.*, 0 as reclevel
	        FROM    contexts c
	        WHERE   root_context_id IS NULL
	        UNION ALL
	        SELECT  cc.*, reclevel+1
	        FROM    q
	        JOIN    contexts cc
	        ON      cc.root_context_id = q.id
        )
        SELECT  q.*, ct.type as typeid, ct.thesaurus_url AS typename, (select label from getconceptlabelsfromurl where concept_url = ct.thesaurus_url and short_name = 'de' limit 1) as typelabel
        FROM    q
        JOIN context_types AS ct
        ON q.context_type_id = ct.id
        ORDER BY reclevel DESC
        ");
        $children = [];
        foreach($rootFields as $key => $field) {
            if($user->can('view_concept_props')) {
                $rootFields[$key]->data =  $this->getData($field->id);
            }
            if(array_key_exists($field->id, $children)) $tmpChildren = $children[$field->id];
            else $tmpChildren = array();
            $rootFields[$key]->children = $tmpChildren;
            $children[$field->root_context_id][] = $field;
            if($field->reclevel != 0) unset($rootFields[$key]);
        }
        return response()->json(array_values($rootFields));
    }

    public function getChoices() {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $rows = DB::table('context_types as c')
        ->select('ca.context_type_id as ctid', 'ca.attribute_id as aid', 'a.datatype', 'a.thesaurus_root_url as root',
            DB::raw("(select label from getconceptlabelsfromurl where concept_url = C.thesaurus_url and short_name = 'de' limit 1) AS title"),
            DB::raw("(select label from getconceptlabelsfromurl where concept_url = A.thesaurus_url and short_name = 'de' limit 1) AS val")
        )
        ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
        ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
        ->where('a.datatype', '=', 'string-sc')
        ->orWhere('a.datatype', '=', 'string-mc')
        ->orWhere('a.datatype', '=', 'epoch')
        ->orderBy('val')
        ->get();
        foreach($rows as &$row) {
            if(!isset($row->root)) continue;
            $rootId = DB::table('th_concept')
                ->select('id')
                ->where('concept_url', '=', $row->root)
                ->first();
            if(!isset($rootId)) continue;
            $rootId = $rootId->id;
            $row->choices = DB::select("
                WITH RECURSIVE
                top AS (
                    SELECT br.broader_id, br.narrower_id, (select label from getconceptlabelsfromid where concept_id = br.broader_id and short_name = 'de' limit 1) as broad,
                            (select label from getconceptlabelsfromid where concept_id = br.narrower_id and short_name = 'de' limit 1) as narr
                    FROM th_broaders br
                    WHERE broader_id = $rootId
                    UNION
                    SELECT br.broader_id, br.narrower_id, (select label from getconceptlabelsfromid where concept_id = br.broader_id and short_name = 'de' limit 1) as broad,
                            (select label from getconceptlabelsfromid where concept_id = br.narrower_id and short_name = 'de' limit 1) as narr
                    FROM top t, th_broaders br
                    WHERE t.narrower_id = br.broader_id
                )
                SELECT *
                FROM top
                ORDER BY narr
            ");
        }
        return response()->json($rows);
    }

    public function duplicate($id) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $toDuplicate = DB::table('contexts')
            ->where('id', $id)
            ->first();
        unset($toDuplicate->id);
        $dupCounter = 0;
        do {
            $dupCounter++;
            $sameName = DB::table('contexts')
                ->where('name', '=', $toDuplicate->name . " ($dupCounter)")
                ->first();
        } while($sameName != null);
        $toDuplicate->name .= " ($dupCounter)";
        $cid = DB::table('contexts')
            ->insertGetId(get_object_vars($toDuplicate));
        $toDuplicate->id = $cid;
        $toDuplicateValues = DB::table('attribute_values')
            ->where('context_id', $id)
            ->get();
        foreach($toDuplicateValues as $value) {
            unset($value->id);
            $value->context_id = $cid;
            Db::table('attribute_values')
                ->insertGetId(get_object_vars($value));
        }
        $toDuplicate->data = $this->getData($cid);
        return response()->json(['obj' => $toDuplicate]);
    }

    public function getArtifacts() {
        return response()->json(
            DB::table('context_types as c')
                ->select('c.thesaurus_url as index', 'ca.context_type_id as ctid', 'ca.attribute_id as aid', 'a.datatype', 'c.type',
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = C.thesaurus_url and short_name = 'de' limit 1) AS title"),
                    DB::raw("(select label from getconceptlabelsfromurl where concept_url = A.thesaurus_url and short_name = 'de' limit 1) AS val")
                )
                ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_type_id')
                ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->where('c.type', '=', '1')
                ->orderBy('val')
                ->get()
        );
    }

    public function getChildren($id) {
        $intId = filter_var($id, FILTER_VALIDATE_INT);
        if($intId === false || $intId <= 0) return;
        $user = \Auth::user();
        if(!$user->can('view_concept_props')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $rows = DB::select(
            "WITH RECURSIVE
            q AS (
                SELECT  c.*
                FROM    contexts c
                WHERE   id = $id
                UNION ALL
                SELECT  cc.*
                FROM    q
                JOIN    contexts cc
                ON      cc.root_context_id = q.id
            )
            SELECT  q.*, ct.type, ct.thesaurus_url AS typename, (select label from getconceptlabelsfromurl where concept_url = ct.thesaurus_url and short_name = 'de' limit 1) as typelabel
            FROM    q
            JOIN context_types AS ct
            ON q.context_type_id = ct.id
            ORDER BY id ASC"
        );
        $roots = array();
        foreach($rows as $row) {
            if(empty($row)) continue;
            $row->data = DB::table('attribute_values as av')->select('av.*', 'a.datatype')->join('attributes as a', 'av.attribute_id', '=', 'a.id')->where('context_id', $row->id)->get();
            if(!empty($row->root_context_id)) $roots[$row->root_context_id][] = $row;
        }
        return response()->json($roots);
    }

    public function add(Request $request) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        //$title = $request->get('title');
        $user = \Auth::user();
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        $id = $request->get('id');
        $name = $request->get('name');
        $ctid = $request->get('cid');
        $cid = -1;
        $isUpdate = $id > 0;
        if($isUpdate) {
            DB::table('contexts')
                ->where('id', $id)
                ->update([
                    'name' => $name,
                    'lat' => $lat,
                    'lng' => $lng,
                    'lasteditor' => $user['name']
                ]);
            $cid = $id;
        } else {
            $ins = [
                    'name' => $name,
                    'context_type_id' => $ctid,
                    'lat' => $lat,
                    'lng' => $lng,
                    'lasteditor' => $user['name']
            ];
            if($request->has('root')) $ins['root'] = $request->get('root');
            $cid = DB::table('contexts')
                ->insertGetId($ins);
        }
        $this->updateOrInsert($request->except(['title', 'lat', 'lng', 'id', 'name', 'cid']), $cid, $isUpdate, $user);
        return response()->json(['fid' => $cid]);
    }

    public function set(Request $request) {
        $user = \Auth::user();
        if(!$user->can('view_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $user = \Auth::user();
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
        $isUpdate = $request->has('realId');
        $name = $request->get('name');
        $ctid = $request->get('ctid');
        if($isUpdate) {
            $realId = $request->get('realId');
            $upd = [
                'name' => $name,
                'lasteditor' => $user['name']
            ];
            if($request->has('lat')) $upd['lat'] = $request->get('lat');
            if($request->has('lng')) $upd['lng'] = $request->get('lng');
            DB::table('contexts')
                ->where('id', $realId)
                ->update($upd);
            $cid = $realId;
            $currAttrs = DB::table('attribute_values')->where('context_id', $realId)->get();
        } else {
            $ins = [
                'name' => $name,
                'context_type_id' => $ctid,
                'lasteditor' => $user['name']
            ];
            if($request->has('root_cid')) $ins['root_cid'] = $request->get('root_cid');
            if($request->has('lat')) $ins['lat'] = $request->get('lat');
            if($request->has('lng')) $ins['lng'] = $request->get('lng');
            $cid = DB::table('contexts')
                ->insertGetId($ins);
        }
        $this->updateOrInsert($request->except('name', 'lat', 'lng', 'root_cid', 'ctid', 'realId'), $cid, $isUpdate, $user);
        return response()->json(['fid' => $cid, 'data' => $this->getData($cid)]);
    }

    public function setIcon(Request $request) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $id = $request->get('id');
        $upd = [];

        if($request->has('icon')) $upd['icon'] = $request->get('icon');
        if($request->has('color')) $upd['color'] = $request->get('color');

        DB::table('contexts')
            ->where('id', $id)
            ->update($upd);
        $icon = DB::table('contexts')
                ->where('id', $id)
                ->first();
        return response()->json([
            'icon' => $icon->icon,
            'color' => $icon->color
        ]);
    }

    public function setPossibility(Request $request) {
        $user = \Auth::user();
        if(!$user->can('duplicate_edit_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        $user = \Auth::user();
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
        $cid = $request->get('cid');
        $aid = $request->get('aid');
        $possibility = $request->get('possibility');

        $where = array(
            ['context_id', '=', $cid],
            ['attribute_id', '=', $aid]
        );
        $isSet = DB::table('attribute_values')
            ->where($where)
            ->count();
        if($isSet == null) { //insert
            DB::table('attribute_values')
                ->insert([
                    'context_id' => $cid,
                    'attribute_id' => $aid,
                    'possibility' => $possibility,
                    'lasteditor' => $user['name']
                ]);
        } else { //update
            DB::table('attribute_values')
                ->where($where)
                ->update(['possibility' => $possibility, 'lasteditor' => $user['name']]);
        }
        return response()->json(DB::table('attribute_values')
            ->where($where)->get());
    }


    public function delete($id) {
        $user = \Auth::user();
        if(!$user->can('delete_move_concepts')) {
            return response([
                'error' => 'You do not have the permission to call this method'
            ], 403);
        }
        DB::select("
            with recursive deletes as
            (
                select id
                from contexts
                where id = $id
                union all
                select c.id
                from contexts as c
                inner join deletes p on f.root_context_id = p.id
            )
            delete from attribute_values where context_id in (select id from deletes)
        ");
        DB::select("
            with recursive deletes as
            (
                select id
                from contexts
                where id = $id
                union all
                select c.id
                from contexts as c
                inner join deletes p on f.root_context_id = p.id
            )
            delete from contexts where id in (select id from deletes)
        ");

        return response()->json(array("id"=>$id));
    }

    public function updateOrInsert($request, $cid, $isUpdate, $user) {
        $currAttrs = DB::table('attribute_values')->where('context_id', $cid)->get();
        foreach($request as $key => $value) {
            $ids = explode("_", $key);
            $aid = $ids[0];
            if(!empty($ids[1])) {
                $oid = $ids[1];
                $tbl = 'option_values';
                $isOption = true;
            } else {
                $tbl = 'attribute_values';
                $isOption = false;
            }
            $datatype = DB::table('attributes')
                ->where('id', '=', $aid)
                ->value('datatype');
            $jsonArr = json_decode($value);
            if($datatype === 'string-sc') $jsonArr = [$jsonArr]; //"convert" to array
            if(is_array($jsonArr)) { //only string-sc and string-mc should be arrays
                if($isUpdate) {
                    $dbEntries = array(
                        ['context_id', $cid],
                        ['attribute_id', $aid]
                    );
                    if($isOption) $dbEntries[] = ['option_id', $oid];
                    $rows = DB::table($tbl)
                        ->where($dbEntries)
                        ->get();
                    foreach($rows as $row) {
                        $alreadySet = false;
                        foreach($jsonArr as $k => $v) {
                            if($datatype === 'list') {
                                $set = $v->name;
                                $val = $row->str_val;
                            } else {
                                $set = DB::table('th_concept')
                                ->where('id', '=', $v->narrower_id)
                                ->value('concept_url');
                                $val = $row->thesaurus_val;
                            }
                            if($val === $set) {
                                unset($jsonArr[$k]);
                                $alreadySet = true;
                                break;
                            }
                        }
                        if(!$alreadySet) {
                            $del = array(
                                ['context_id', $cid],
                                ['attribute_id', $aid]
                            );
                            if($datatype === 'list') $del[] = ['str_val', $row->str_val];
                            else $del[] = ['thesaurus_val', $row->thesaurus_val];
                            if($isOption) $del[] = ['option_id', $oid];
                            DB::table($tbl)
                                ->where($del)
                                ->delete();
                        }
                    }
                }
                foreach($jsonArr as $v) {
                    $vals = array(
                        'context_id' => $cid,
                        'attribute_id' => $aid,
                        'lasteditor' => $user['name']
                    );
                    if($datatype === 'list') {
                        $vals['str_val'] = $v->name;
                    } else {
                        $set = DB::table('th_concept')
                            ->where('id', '=', $v->narrower_id)
                            ->value('concept_url');
                        $vals['thesaurus_val'] = $set;
                    }
                    if($isOption) $vals['option_id'] = $oid;
                    DB::table($tbl)
                        ->insert($vals);
                }
            } else {
                if($isUpdate) {
                    $alreadySet = false;
                    $attr;
                    foreach($currAttrs as $currKey => $currVal) {
                        if($aid == $currVal->attribute_id) {
                            $alreadySet = true;
                            $attr = $currVal;
                            unset($currAttrs[$currKey]);
                            break;
                        }
                    }
                    if($alreadySet) {
                        $data = array('lasteditor' => $user['name']);
                        $data['str_val'] = $this->parseValue($jsonArr, $value, $datatype);
                        DB::table('attribute_values')
                                ->where([
                                    ['context_id', '=', $attr->context_id],
                                    ['attribute_id', '=', $attr->attribute_id],
                                    ['id', '=', $attr->id]
                                ])
                            ->update($data);
                    } else {
                        $vals = array(
                            'context_id' => $cid,
                            'attribute_id' => $aid,
                            'str_val' => $value,
                            'lasteditor' => $user['name']
                        );
                        $vals['str_val'] = $this->parseValue($jsonArr, $value, $datatype);
                        if($isOption) $vals['option_id'] = $oid;
                        DB::table($tbl)
                            ->insert($vals);
                    }
                } else {
                    $vals = array(
                        'context_id' => $cid,
                        'attribute_id' => $aid,
                        'lasteditor' => $user['name']
                    );
                    $vals['str_val'] = $this->parseValue($jsonArr, $value, $datatype);
                    if($isOption) $vals['option_id'] = $oid;
                    DB::table($tbl)
                        ->insert($vals);
                }
            }
        }
    }

    private function parseValue($jsonArr, $value, $datatype) {
        if(is_object($jsonArr)) {
            if($datatype === 'epoch') {
                if(property_exists($jsonArr, 'start')) {
                    $jsonArr->start = ($jsonArr->startLabel === 'n. Chr.') ? $jsonArr->start : -$jsonArr->start;
                } else {
                    $jsonArr->start = '';
                }
                if(property_exists($jsonArr, 'end')) {
                    $jsonArr->end = ($jsonArr->endLabel === 'n. Chr.') ? $jsonArr->end : -$jsonArr->end;
                } else {
                    $jsonArr->end = '';
                }
                if(property_exists($jsonArr, 'epoch') && $jsonArr->epoch != null) {
                    $jsonArr->epochUrl = DB::table('th_concept')
                        ->where('id', '=', $jsonArr->epoch->narrower_id)
                        ->value('concept_url');
                } else {
                    $jsonArr->epochUrl = '';
                }
                $tmpVal = $jsonArr->start.";".$jsonArr->end.";".$jsonArr->epochUrl;
            } else if($datatype === 'dimension') {
                if(property_exists($jsonArr, 'B')) {
                    $b = $jsonArr->B;
                } else {
                    $b = '';
                }
                if(property_exists($jsonArr, 'H')) {
                    $h = $jsonArr->H;
                } else {
                    $h = '';
                }
                if(property_exists($jsonArr, 'T')) {
                    $t = $jsonArr->T;
                } else {
                    $t = '';
                }
                if(property_exists($jsonArr, 'unit')) {
                    $unit = $jsonArr->unit;
                } else {
                    $unit = '';
                }
                $tmpVal = $b.";".$h.";".$t.";".$unit;
            }
        } else {
            $tmpVal = $value;
        }
        return $tmpVal;
    }
}
