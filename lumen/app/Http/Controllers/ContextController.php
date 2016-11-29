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

    private function getData($id) {
        $data = DB::table('context_values as cv')->select('cv.*', 'a.datatype', 'a.thesaurus_root_id')->join('attributes as a', 'cv.attribute_id', '=', 'a.id')->where('find_id', $id)->get();
        foreach($data as &$attr) {
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
                                    DB::raw("public.\"getLabelForId\"(concept_url, 'de') as narr")
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
            $rootFields[$key]->data =  $this->getData($field->id);
            if(array_key_exists($field->id, $children)) $tmpChildren = $children[$field->id];
            else $tmpChildren = array();
            $rootFields[$key]->children = $tmpChildren;
            $children[$field->root][] = $field;
            if($field->reclevel != 0) unset($rootFields[$key]);
        }
        return response()->json(array_values($rootFields));
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

    public function getChoices() {
        $rows = DB::table('context_types as c')
        ->select('ca.context_id as cid', 'ca.attribute_id as aid', 'a.datatype', 'a.thesaurus_root_id as root',
            DB::raw("public.\"getLabelForId\"(C.thesaurus_id, 'de') AS title, public.\"getLabelForId\"(A.thesaurus_id, 'de') AS val")
        )
        ->leftJoin('context_attributes as ca', 'c.id', '=', 'ca.context_id')
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
        return response()->json($rows);
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

    public function duplicate($id) {
        $toDuplicate = DB::table('finds')
            ->where('id', $id)
            ->first();
        unset($toDuplicate->id);
        $dupCounter = 0;
        do {
            $dupCounter++;
            $sameName = DB::table('finds')
                ->where('name', '=', $toDuplicate->name . " ($dupCounter)")
                ->first();
        } while($sameName != null);
        $toDuplicate->name .= " ($dupCounter)";
        $fid = DB::table('finds')
            ->insertGetId(get_object_vars($toDuplicate));
        $toDuplicate->id = $fid;
        $toDuplicateValues = DB::table('context_values')
            ->where('find_id', $id)
            ->get();
        foreach($toDuplicateValues as $value) {
            unset($value->id);
            $value->find_id = $fid;
            Db::table('context_values')
                ->insertGetId(get_object_vars($value));
        }
        $toDuplicate->data = $this->getData($fid);
        return response()->json(['obj' => $toDuplicate]);
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
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
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
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
        $isUpdate = $request->has('realId');
        $name = $request->get('name');
        $cid = $request->get('cid');
        if($isUpdate) {
            $realId = $request->get('realId');
            $upd = [
                'name' => $name,
                'lasteditor' => $user['name']
            ];
            if($request->has('lat')) $upd['lat'] = $request->get('lat');
            if($request->has('lng')) $upd['lng'] = $request->get('lng');
            DB::table('finds')
                ->where('id', $realId)
                ->update($upd);
            $fid = $realId;
            $currAttrs = DB::table('context_values')->where('find_id', $realId)->get();
        } else {
            $ins = [
                'name' => $name,
                'context_id' => $cid,
                'lasteditor' => $user['name']
            ];
            if($request->has('root')) $ins['root'] = $request->get('root');
            if($request->has('lat')) $ins['lat'] = $request->get('lat');
            if($request->has('lng')) $ins['lng'] = $request->get('lng');
            $fid = DB::table('finds')
                ->insertGetId($ins);
        }
        $this->updateOrInsert($request->except('name', 'lat', 'lng', 'root', 'cid', 'realId'), $fid, $isUpdate, $user);
        return response()->json(['fid' => $fid, 'data' => $this->getData($fid)]);
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
        $user = \Auth::user();
        if($user == null) $user = ['name' => 'postgres']; //TODO remove after user auth has been fixed!
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
                    'possibility' => $possibility,
                    'lasteditor' => $user['name']
                ]);
        } else { //update
            DB::table('context_values')
                ->where($where)
                ->update(['possibility' => $possibility, 'lasteditor' => $user['name']]);
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
                        $vals['str_val'] = $this->parseValue($jsonArr, $value, $datatype);
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
