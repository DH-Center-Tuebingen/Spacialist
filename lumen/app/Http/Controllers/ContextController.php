<?php

namespace App\Http\Controllers;
use Log;
use App\User;
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

    public function get() {
        return response()->json(
            DB::table('context_types as c')
                ->select('c.thesaurus_id as index', 'ca.context_id as cid', 'ca.attribute_id as aid', 'a.datatype', 'c.type',
                    DB::raw("public.\"getLabelForId\"(C.thesaurus_id, 'de') AS title, public.\"getLabelForId\"(A.thesaurus_id, 'de') AS val")
                )
                ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_id')
                ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->where('c.type', '=', '0')
                ->orderBy('val')
                ->get()
        );
    }

    public function getRecursive() {
        $rootFields = DB::select("
        WITH RECURSIVE
        q AS (
	        SELECT  f.*, 0 as reclevel
	        FROM    finds f
	        WHERE   root IS NULL
	        UNION ALL
	        SELECT  fc.*, reclevel+1
	        FROM    q
	        JOIN    finds fc
	        ON      fc.root = q.id
        )
        SELECT  q.*, ct.type as typeid, ct.thesaurus_id AS typename, public.\"getLabelForId\"(ct.thesaurus_id, 'de') as typelabel
        FROM    q
        JOIN context_types AS ct
        ON q.context_id = ct.id
        ORDER BY reclevel DESC
        ");
        $children = [];
        foreach($rootFields as $key => $field) {
            $rootFields[$key]->data =  DB::table('context_values as cv')->select('cv.*', 'a.datatype', 'a.thesaurus_root_id')->join('attributes as a', 'cv.attribute_id', '=', 'a.id')->where('find_id', $field->id)->get();
            foreach($rootFields[$key]->data as &$attr) {
                if($attr->datatype == 'literature') {
                    $attr->literature_info = DB::table('bib_tex')->where('id', $attr->str_val)->first();
                } else if($attr->datatype == 'string-sc' || $attr->datatype == 'string-mc') {
                    $attr->val = DB::table('th_concept')
                        ->select('id as narrower_id',
                            DB::raw("public.\"getLabelForId\"(concept_url, 'de') as narr")
                        )
                        ->where('concept_url', '=', $attr->thesaurus_val)
                        ->first();
                } else if($attr->datatype == 'dimension') {
                    $elems = explode(';', $attr->str_val, 4);
                    $attr->val = json_encode(['B' => intval($elems[0]), 'H' => intval($elems[1]), 'T' => intval($elems[2]), 'unit' => intval($elems[3])]);
                } else if($attr->datatype == 'epoch') {
                    $elems = explode(';', $attr->str_val, 3);
                    $start = intval($elems[0]);
                    $end = intval($elems[1]);
                    $thUri = $elems[2];
                    $startLabel = $start < 0 ? 'v. Chr.' : 'n. Chr.';
                    $endLabel = $end < 0 ? 'v. Chr.' : 'n. Chr.';
                    $attr->val = json_encode([
                        'startLabel' => $startLabel,
                        'start' => $start,
                        'endLabel' => $endLabel,
                        'end' => $end,
                        'epoch' => DB::table('th_concept')
                                    ->select('id as narrower_id',
                                        DB::raw("public.\"getLabelForId\"(concept_url, 'de') as narr")
                                    )
                                    ->where('concept_url', '=', $thUri)
                                    ->first()
                    ]);
                }
            }
            if(array_key_exists($field->id, $children)) $tmpChildren = $children[$field->id];
            else $tmpChildren = array();
            $rootFields[$key]->children = $tmpChildren;
            $children[$field->root][] = $field;
            if($field->reclevel != 0) unset($rootFields[$key]);
        }
        return response()->json($rootFields);
    }

    public function getAll() {
        return response()->json(
            [
                'finds' =>
                    DB::table('finds')
                    ->select('name', 'context_id as cid, id')
                    ->get()
            ]
        );
    }

    public function getAttributes($id) {
        $rows = DB::table('context_types as c')
        ->select('ca.context_id as cid', 'ca.attribute_id as aid', 'a.datatype', 'a.thesaurus_root_id as root',
            DB::raw("public.\"getLabelForId\"(C.thesaurus_id, 'de') AS title, public.\"getLabelForId\"(A.thesaurus_id, 'de') AS val")
        )
        ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_id')
        ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
        //->where('c.id', '=', $id)
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
                    SELECT br.broader_id, br.narrower_id, public.\"getLabeForTmpid\"(br.broader_id, 'de') as broad, public.\"getLabeForTmpid\"(br.narrower_id, 'de') as narr
                    FROM th_broaders br
                    WHERE broader_id = $rootId
                    UNION
                    SELECT br.broader_id, br.narrower_id, public.\"getLabeForTmpid\"(br.broader_id, 'de') as broad, public.\"getLabeForTmpid\"(br.narrower_id, 'de') as narr
                    FROM top t, th_broaders br
                    WHERE t.narrower_id = br.broader_id
                )
                SELECT *
                FROM top
                ORDER BY narr
            ");
        }
        // TODO get option attributes
        /*foreach($rows as &$row) {
            if($row->datatype == "option") {
                $row->attributes = getOptionsForId($db, $row['root']);
            }
        }*/
        return response()->json($rows);
    }

    public function getType($id) {
        $finds = DB::table('finds')->get();
        foreach($finds as &$find) {
            $attribs = DB::table('context_values as CV')
                    ->join('attributes as A', 'CV.attribute_id', 'A.id')
                    ->where('find_id', $find->id)
                    ->select('CV.*', 'A.datatype', 'A.thesaurus_root_id')
                    ->get();
            foreach($attribs as &$attr) {
                if($attr->datatype == 'literature') {
                    $attr->literature_info = DB::table('bib_tex')->where('id', $attr->str_val)->first();
                } else if($attr->datatype == 'string-sc' || $attr->datatype == 'string-mc') {
                    $attr->val = DB::table('th_concept')
                        ->select('id as narrower_id',
                            DB::raw("public.\"getLabelForId\"(concept_url, 'de') as narr")
                        )
                        ->where('concept_url', '=', $attr->thesaurus_val)
                        ->first();
                }
            }
            $find->attributes = $attribs;
        }
        return response()->json($finds);
    }

    public function getArtifacts() {
        return response()->json(
            DB::table('context_types as c')
                ->select('c.thesaurus_id as index', 'ca.context_id as cid', 'ca.attribute_id as aid', 'a.datatype', 'c.type',
                    DB::raw(
                        "public.\"getLabelForId\"(C.thesaurus_id, 'de') AS title, public.\"getLabelForId\"(A.thesaurus_id, 'de') AS val"
                    )
                )
                ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_id')
                ->leftJoin('attributes as a', 'ca.attribute_id', '=', 'a.id')
                ->where('c.type', '=', '1')
                ->orderBy('val')
                ->get()
        );
    }

    public function getChildren($id) {
        $intId = filter_var($id, FILTER_VALIDATE_INT);
        if($intId === false || $intId <= 0) return;
        $rows = DB::select(
            "WITH RECURSIVE
            q AS (
                SELECT  f.*
                FROM    finds f
                WHERE   id = $id
                UNION ALL
                SELECT  fc.*
                FROM    q
                JOIN    finds fc
                ON      fc.root = q.id
            )
            SELECT  q.*, ct.type, ct.thesaurus_id AS typename, public.\"getLabelForId\"(ct.thesaurus_id, 'de') as typelabel
            FROM    q
            JOIN context_types AS ct
            ON q.context_id = ct.id
            ORDER BY id ASC"
        );
        $roots = array();
        foreach($rows as $row) {
            if(empty($row)) continue;
            $row->data = DB::table('context_values as cv')->select('cv.*', 'a.datatype')->join('attributes as a', 'cv.attribute_id', '=', 'a.id')->where('find_id', $row->id)->get();
            if(!empty($row->root)) $roots[$row->root][] = $row;
        }
        return response()->json($roots);
    }

    public function add(Request $request) {
        //$title = $request->get('title');
        $user = \Auth::user();
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        $id = $request->get('id');
        $name = $request->get('name');
        $cid = $request->get('cid');
        $fid = -1;
        $isUpdate = $id > 0;
        if($isUpdate) {
            DB::table('finds')
                ->where('id', $id)
                ->update([
                    'name' => $name,
                    'lat' => $lat,
                    'lng' => $lng,
                    'lasteditor' => $user['name']
                ]);
            $fid = $id;
        } else {
            $ins = [
                    'name' => $name,
                    'context_id' => $cid,
                    'lat' => $lat,
                    'lng' => $lng,
                    'lasteditor' => $user['name']
            ];
            if($request->has('root')) $ins['root'] = $request->get('root');
            $fid = DB::table('finds')
                ->insertGetId($ins);
        }
        $this->updateOrInsert($request->except(['title', 'lat', 'lng', 'id', 'name', 'cid']), $fid, $isUpdate, $user);
        return response()->json(['fid' => $fid]);
    }

    public function set(Request $request) {
        $user = \Auth::user();
        return response()->json($user['name']);
        $isUpdate = $request->has('realId');
        $name = $request->get('name');
        $root = $request->get('root');
        $cid = $request->get('cid');
        if($isUpdate) {
            $realId = $request->get('realId');
            DB::table('finds')
                ->where('id', $realId)
                ->update(['name' => $name, 'lasteditor' => $user['name']]);
            $fid = $realId;
            $currAttrs = DB::table('context_values')->where('find_id', $realId)->get();
        } else {
            $fid = DB::table('finds')
                ->insertGetId([
                    'name' => $name,
                    'context_id' => $cid,
                    'root' => $root,
                    'lasteditor' => $user['name']
                ]);
        }
        $this->updateOrInsert($request->except('name', 'root', 'cid', 'realId'), $fid, $isUpdate, $user);
        return response()->json(['fid' => $fid]);
    }

    public function setIcon(Request $request) {
        $id = $request->get('id');
        $upd = [];

        if($request->has('icon')) $upd['icon'] = $request->get('icon');
        if($request->has('color')) $upd['color'] = $request->get('color');

        DB::table('finds')
            ->where('id', $id)
            ->update($upd);
        $icon = DB::table('finds')
                ->where('id', $id)
                ->first();
        return response()->json([
            'icon' => $icon->icon,
            'color' => $icon->color
        ]);
    }

    public function setPossibility(Request $request) {
        $fid = $request->get('fid');
        $aid = $request->get('aid');
        $possibility = $request->get('possibility');

        $where = array(
            ['find_id', '=', $fid],
            ['attribute_id', '=', $aid]
        );
        $isSet = DB::table('context_values')
            ->where($where)
            ->count();
        if($isSet == null) { //insert
            DB::table('context_values')
                ->insert([
                    'find_id' => $fid,
                    'attribute_id' => $aid,
                    'possibility' => $possibility
                ]);
        } else { //update
            DB::table('context_values')
                ->where($where)
                ->update(['possibility' => $possibility]);
        }
        return response()->json(DB::table('context_values')
            ->where($where)->get());
    }


    public function delete($id) {
        DB::select("
            with recursive deletes as
            (
                select id
                from finds
                where id = $id
                union all
                select f.id
                from finds as f
                inner join deletes p on f.root = p.id
            )
            delete from context_values where find_id in (select id from deletes)
        ");
        DB::select("
            with recursive deletes as
            (
                select id
                from finds
                where id = $id
                union all
                select f.id
                from finds as f
                inner join deletes p on f.root = p.id
            )
            delete from finds where id in (select id from deletes)
        ");

        return response()->json(array("id"=>$id));
    }

    public function updateOrInsert($request, $fid, $isUpdate, $user) {
        $currAttrs = DB::table('context_values')->where('find_id', $fid)->get();
        foreach($request as $key => $value) {
            $ids = explode("_", $key);
            $aid = $ids[0];
            if(!empty($ids[1])) {
                $oid = $ids[1];
                $tbl = 'option_values';
                $isOption = true;
            } else {
                $tbl = 'context_values';
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
                        ['find_id', $fid],
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
                                ['find_id', $fid],
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
                        'find_id' => $fid,
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
                /*$vals = array(
                    ['find_id', '=', $fid],
                    ['attribute_id', '=', $aid]
                );
                if($isOption) $vals[] = ['option_id', '=', $oid];
                $valId = DB::table($tbl)
                    ->where($vals)
                    ->value('id');
                if($valId > 0) {
                    DB::table($tbl)
                        ->where('id', '=', $valId)
                        ->update(['str_val' => $value]);
                }*/
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
                    $data = array('str_val' => $value, 'lasteditor' => $user['name']);
                    DB::table('context_values')
                            ->where([
                                ['find_id', '=', $attr->find_id],
                                ['attribute_id', '=', $attr->attribute_id],
                                ['id', '=', $attr->id]
                            ])
                        ->update($data);
                } else {
                        $vals = array(
                            'find_id' => $fid,
                            'attribute_id' => $aid,
                            'str_val' => $value,
                            'lasteditor' => $user['name']
                        );
                        if(is_object($jsonArr)) {
                            if($datatype === 'epoch') {
                                $jsonArr->start = ($jsonArr->startLabel === 'n. Chr.') ? $jsonArr->start : -$jsonArr->start;
                                $jsonArr->end = ($jsonArr->endLabel === 'n. Chr.') ? $jsonArr->end : -$jsonArr->end;
                                $jsonArr->epochUrl = DB::table('th_concept')
                                    ->where('id', '=', $jsonArr->epoch->narrower_id)
                                    ->value('concept_url');
                                $tmpVal = $jsonArr->start.";".$jsonArr->end.";".$jsonArr->epochUrl;
                            } else if($datatype === 'dimension') {
                                $tmpVal = $jsonArr->B.";".$jsonArr->H.";".$jsonArr->T.";".$jsonArr->unit;
                            }
                            $vals['str_val'] = $tmpVal;
                        } else {
                            $vals['str_val'] = $value;
                        }
                        if($isOption) $vals['option_id'] = $oid;
                        DB::table($tbl)
                            ->insert($vals);
                }
            } else {
                    $vals = array(
                        'find_id' => $fid,
                        'attribute_id' => $aid,
                        'lasteditor' => $user['name']
                    );
                    if(is_object($jsonArr)) {
                        if($datatype === 'epoch') {
                            $jsonArr->start = ($jsonArr->startLabel === 'n. Chr.') ? $jsonArr->start : -$jsonArr->start;
                            $jsonArr->end = ($jsonArr->endLabel === 'n. Chr.') ? $jsonArr->end : -$jsonArr->end;
                            $jsonArr->epochUrl = DB::table('th_concept')
                                ->where('id', '=', $jsonArr->epoch->narrower_id)
                                ->value('concept_url');
                            $tmpVal = $jsonArr->start.";".$jsonArr->end.";".$jsonArr->epochUrl;
                        } else if($datatype === 'dimension') {
                            $tmpVal = $jsonArr->B.";".$jsonArr->H.";".$jsonArr->T.";".$jsonArr->unit;
                        }
                        $vals['str_val'] = $tmpVal;
                    } else {
                        $vals['str_val'] = $value;
                    }
                    if($isOption) $vals['option_id'] = $oid;
                    DB::table($tbl)
                        ->insert($vals);
            }
        }
            }
    }
}
