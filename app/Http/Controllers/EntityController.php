<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\AttributeValue;
use App\Entity;
use App\EntityAttribute;
use App\EntityFile;
use App\EntityType;
use App\File;
use App\Reference;
use App\Exceptions\AmbiguousValueException;
use App\Exceptions\AttributeImportException;
use App\Exceptions\ImportException;
use App\Exceptions\InvalidDataException;
use App\Exceptions\Structs\AttributeImportExceptionStruct;
use App\Exceptions\Structs\ImportExceptionStruct;
use App\Import\EntityImporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use ZipArchive;

class EntityController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    // GET

    public function getTopEntities() {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get entities'),
            ], 403);
        }
        $roots = Entity::getEntitiesByParent(null);

        return response()->json($roots);
    }

    public function getEntity($id) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get a specific entity'),
            ], 403);
        }
        try{
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }

        return response()->json($entity);
    }

    public function getDataForEntityType(Request $request, $etid, $aid) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_type_read') || !$user->can('entity_data_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s data'),
            ], 403);
        }
        try{
            $entityType = EntityType::findOrFail($etid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity type does not exist'),
            ], 400);
        }
        try{
            Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This attribute does not exist'),
            ], 400);
        }
        $constraints = $request->query();
        $entities = Entity::where('entity_type_id', $etid);
        foreach($constraints as $relation => $cons) {
            if($cons == 'has') {
                $entities->has($relation);
            } else if($cons == 'hasnot') {
                $entities->doesntHave($relation);
            }
        }
        $entities = $entities->get();
        $entityIds = $entities->pluck('id')->toArray();
        $values = AttributeValue::whereHas('attribute', function (Builder $q) {
            $q->where('datatype', '!=', 'sql');
        })
            ->whereIn('entity_id', $entityIds)
            ->where('attribute_id', $aid)
            ->get();
        $data = [];
        foreach($values as $value) {
            switch($value->attribute->datatype) {
                case 'entity':
                    $entity = Entity::find($value->entity_val)->name;
                    $value->name = $entity;
                    $value->value = $entity;
                    break;
                case 'entity-mc':
                    break;
                default:
                    $value->value = $value->getValue();
                    break;
            }
            $data[$value->entity_id] = $value;
        }

        // The SQL handling is not broken or fixed here, as this controller is only
        // used by the map plugin.
        $sqls = EntityAttribute::whereHas('attribute', function (Builder $q) {
            $q->where('datatype', 'sql');
        })
            ->where('entity_type_id', $etid)
            ->where('attribute_id', $aid)
            ->get();

        foreach($sqls as $sql) {
            // if entity_id is referenced several times
            // add an incrementing counter, so the
            // references are unique (required by PDO)
            $cnt = substr_count($sql->attribute->text, ':entity_id');
            if($cnt > 1) {
                $i = 0;
                $text = preg_replace_callback('/:entity_id/', function ($matches) use (&$i) {
                    return $matches[0] . '_' . $i++;
                }, $sql->attribute->text);
            }else{
                $text = $sql->attribute->text;
            }
            foreach($entityIds as $eid) {
                $safes = [];
                if($cnt > 1) {
                    for($i = 0; $i < $cnt; $i++) {
                        $safes[':entity_id_' . $i] = $eid;
                    }
                }else{
                    $safes = [
                        ':entity_id' => $eid,
                    ];
                }

                DB::beginTransaction();
                $sqlValue = DB::select($text, $safes);
                DB::rollBack();

                // Check if only one result exists
                if(count($sqlValue) === 1) {
                    // Get all column indices (keys) using the first row
                    $valueKeys = array_keys(get_object_vars($sqlValue[0]));
                    // Check if also only one key/column exists
                    if(count($valueKeys) === 1) {
                        // If only one row and one column exist,
                        // return plain value instead of array
                        $firstKey = $valueKeys[0];
                        $sqlValue = $sqlValue[0]->{$firstKey};
                    }
                }
                $data[$eid] = [
                    'value' => $sqlValue,
                ];
            }
        }

        return response()->json($data);
    }

    public function getData(int $id, ?int $aid = null) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_data_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s data'),
            ], 403);
        }

        $entity = null;
        try{
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }
        if(isset($aid)) {
            try {
                Attribute::findOrFail($aid);
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('This attribute does not exist'),
                ], 400);
            }
        }
        $data = $entity->getData($aid);
        return response()->json($data);
    }

    public function getMetadata($id) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_data_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s metadata')
            ], 403);
        }

        try{
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }
        return response()->json($entity->getAllMetadata());
    }

    public function getParentIds($id) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity\'s parent id\'s'),
            ], 403);
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }
        return response()->json($entity->parentIds);
    }

    public function getEntitiesByParent($id) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to get an entity set'),
            ], 403);
        }

        return Entity::getEntitiesByParent($id);
    }

    // POST

    public function addEntity(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_create')) {
            return response()->json([
                'error' => __('You do not have the permission to add a new entity'),
            ], 403);
        }
        $this->validate($request, Entity::rules);

        $fields = $request->only(array_keys(Entity::rules));
        $etid = $request->get('entity_type_id');
        $reid = $request->get('root_entity_id');
        $rank = $request->get('rank');

        $res = Entity::create($fields, $etid, $user, $reid, $rank);

        if($res['type'] === 'entity') {
            return response()->json($res['entity'], 201);
        }else{
            return response()->json([
                'error' => $res['msg'],
            ], $res['code']);
        }
    }

    public function duplicateEntity(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('entity_create')) {
            return response()->json([
                'error' => __('You do not have the permission to duplicate an entity'),
            ], 403);
        }

        try{
            $entity = Entity::without(['user', 'parentIds', 'parentNames'])->findOrFail($id);
            unset($entity->comments_count);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }

        $duplicate = $entity->replicate();
        $duplicate->created_at = Carbon::now();
        if(sp_has_plugin('Map')) {
            $duplicate->geodata_id = null;
        }
        if(isset($duplicate->root_entity_id)) {
            $duplicate->rank = Entity::where('root_entity_id', $duplicate->root_entity_id)->max('rank') + 1;
        }else{
            $duplicate->rank = Entity::whereNull('root_entity_id')->max('rank') + 1;
        }
        $duplicate->user_id = $user->id;
        $duplicate->name = sp_copyname($duplicate->name);
        $duplicate->save();

        // Files, bibliographies, attribute_values
        if(sp_has_plugin('File')) {
            $fileLinks = EntityFile::where('entity_id', $entity->id)->get();
            foreach($fileLinks as $fileLink) {
                $newLink = $fileLink->replicate();
                $newLink->entity_id = $duplicate->id;
                $newLink->user_id = $user->id;
                $newLink->save();
            }
        }
        $refs = Reference::where('entity_id', $entity->id)->get();
        foreach($refs as $ref) {
            $newLink = $ref->replicate();
            $newLink->entity_id = $duplicate->id;
            $newLink->user_id = $user->id;
            $newLink->created_at = Carbon::now();
            $newLink->save();
        }
        $values = AttributeValue::where('entity_id', $entity->id)->get();
        foreach($values as $val) {
            unset($val->comments_count);
            $newValue = $val->replicate();
            $newValue->entity_id = $duplicate->id;
            $newValue->user_id = $user->id;
            $newValue->created_at = Carbon::now();
            $newValue->save();
        }

        return response()->json($duplicate, 201);
    }

    private function verifyImportData(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_create') || !$user->can('entity_write')) {
            return response()->json([
                'error' => __('You do not have the permission to import entity data'),
            ], 403);
        }
        $this->validate($request, [
            'file' => 'required|file',
            'metadata' => 'required|json',
            'data' => 'required|json',
        ]);

        $file = $request->file('file');

        if(!$file || !$file->isValid()) {
            return response()->json([
                'error' => __('entity-importer.invalid-data', ['column' => 'file', 'value' => $file === null ? 'null' : 'invalid']),
            ], 422);
        }

        $filepath = $file->getRealPath();
        File::removeBomIfNecessary($filepath);

        return null;
    }

    public function validateImportData(Request $request) {
        $errorResponse = $this->verifyImportData($request);
        if($errorResponse) {
            return $errorResponse;
        }

        $file = $request->file('file');
        $filepath = $file->getRealPath();
        $metadata = json_decode($request->get('metadata'), true);
        $data = json_decode($request->get('data'), true);

        $entityImport = new EntityImporter($metadata, $data);
        $resolver = $entityImport->validateImportData($filepath);

        return response()->json([
            'errors' => $resolver->getErrors(),
            'summary' => $resolver->getSummary(),
        ]);
    }

    /**
     * TODO: Move this functionality into the EntityImporter class.
     */
    public function importData(Request $request) {
        $errorResponse = $this->verifyImportData($request);
        if($errorResponse) {
            return $errorResponse;
        }

        $file = $request->file('file');
        $filepath = $file->getRealPath();
        $metadata = json_decode($request->get('metadata'), true);
        $data = json_decode($request->get('data'), true);
        $handle = fopen($filepath, 'r');

        $hasHeaderRow = $metadata["has_header_row"];

        // Data values
        $nameColumn = trim($data['name_column']);
        $parentColumn = isset($data['parent_column']) ? trim($data['parent_column']) : null;
        $entityTypeId = trim($data['entity_type_id']);
        $attributesMapping = array_map(fn ($col) => trim($col), $data['attributes']);

        $headerRow = null;
        $hasParent = false;
        $attributeIdToColumnIdxMapping = [];
        $attributeTypes = [];
        $changedEntities = [];

        DB::beginTransaction();

        $affectedRows = 0;
        $parentIdx = null;
        $nameIdx = null;

        // Getting headers
        if(($row = fgetcsv($handle, 0, $metadata['delimiter'])) !== false) {
            $row = sp_trim_array($row);
            try{
                $headerRow = $row;
                for($i = 0; $i < count($row); $i++) {
                    // Use the provided column name or the column number
                    $columnName = $hasHeaderRow ? $row[$i] : "#".($i + 1);

                    if($columnName == $nameColumn) {
                        $nameIdx = $i;
                    } else if(isset($parentColumn) && $columnName == $parentColumn) {
                        $parentIdx = $i;
                        $hasParent = true;
                    }

                    foreach($attributesMapping as $id => $a) {
                        if($a == $columnName) {
                            $attributeIdToColumnIdxMapping[$id] = $i;
                            $attributeTypes[$id] = Attribute::findOrFail($id)->datatype;
                            break;
                        }
                    }
                }
            } catch(ModelNotFoundException $e) {
                DB::rollBack();
                $ids = $e->getIds();
                return response()->json([
                    'error' => __('entity-importer.attribute-id-does-not-exist', ['attributes' => join(', ', $ids)]),
                    'data' => new ImportExceptionStruct(),
                ], 400);
            }
        }

        // When we have no header row, we need to rewind the file handle
        if(!$hasHeaderRow){
            rewind($handle);
        }

        //Processing rows
        while(($row = fgetcsv($handle, 0, $metadata['delimiter'])) !== false) {
            $row = sp_trim_array($row);
            $affectedRows++;

            if(!isset($nameIdx)) {
                throw new ImportException(
                    "Name column '" . $nameColumn . "' could not be found in CSV file",
                    400,
                    new ImportExceptionStruct(on: $nameColumn)
                );
            }

            $rootEntityPath = $hasParent ? $row[$parentIdx] : null;
            $entityName = $row[$nameIdx];
            $entityPath = $entityName;
            $entityId = null;

            $errorResponseData = new ImportExceptionStruct(
                count: count($changedEntities) + 1,
                entry: $entityName,
            );

            if($hasParent && !empty($rootEntityPath)) {

                $entityPath = implode("\\\\", [$rootEntityPath, $entityName]);

                $errorResponseData->on = $headerRow[$parentIdx];
                $errorResponseData->on_index = $parentIdx + 1;
                $errorResponseData->on_value = $row[$parentIdx];

                try{
                    $parentEntity = Entity::getFromPath($rootEntityPath);
                    if(!isset($parentEntity)) {
                        DB::rollBack();
                        return response()->json([
                            'error' => __('Parent entity does not exist'),
                            'data' => $errorResponseData
                        ], 400);
                    }
                } catch(AmbiguousValueException $ave) {
                    DB::rollBack();
                    return response()->json([
                        'error' => __($ave->getMessage()),
                        'data' => $errorResponseData,
                    ], 400);
                }
            }

            try{
                $entityId = Entity::getFromPath($entityPath);
            } catch(AmbiguousValueException $ave) {
                DB::rollBack();
                return response()->json([
                    'error' => __($ave->getMessage()),
                    'data' => $errorResponseData,
                ], 400);
            }
            try{
                $user = auth()->user();
                if($entityId == null) {
                    $entity = $this->createImportedEntity($entityName, $rootEntityPath, $entityTypeId, $user);

                    // If create entity fails, return error
                    if($entity["type"] !== "entity") {
                        DB::rollBack();
                        return response()->json([
                            'error' => $entity['msg'],
                            'data' => [
                                'count' => count($changedEntities) + 1,
                                'entry' => $entityName,
                                'on' => __('Create Entity from given data'),
                            ],
                        ], $entity['code']);
                    }

                    $entityId = $entity['entity']->id;
                }

                $this->setOrUpdateImportedAttributes($entityId, $row, $headerRow, $attributeIdToColumnIdxMapping, $attributeTypes, $user);
                $changedEntities[] = $entityId;
            } catch(AttributeImportException $e) {
                DB::rollBack();
                return response()->json($e->toImportExceptionObject(count($changedEntities) + 1, $entityName), 400);
            } catch(ImportException $e) {
                DB::rollBack();
                return response()->json(
                    [
                        'error' => $e->getMessage(),
                        'data' => $e->getData()
                    ],
                    400
                );
            } catch(Exception $e) {
                DB::rollBack();
                return response()->json(
                    [
                        'error' => $e->getMessage(),
                        'data' => $errorResponseData
                    ],
                    400
                );
            }
        }

        if($affectedRows === 0) {
            DB::rollBack();
            return response()->json([
                'error' => __('entity-importer.empty'),
            ], 400);
        }

        fclose($handle);
        DB::commit();
        return response()->json($changedEntities, 201);
    }

    function exportEntityTree($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_read') || !$user->can('entity_share')) {
            return response()->json([
                'error' => __('You do not have the permission to export an entity tree'),
            ], 403);
        }

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }

        $entities = $entity->getAllChildren();
        $tmpDir = File::getUniqueTemporaryDirectoryName();
        Storage::disk('private')->makeDirectory($tmpDir);
        try {
            $files = $this->createImportFilesForDistinctEntityTypes($entities, $tmpDir);

            if(count($files) == 0) {
                return response()->json([
                    'error' => __('No entities found'),
                ], 400);
            }

            // We replace all non-alphanumeric characters to avoid path traversal attacks
            $filename = 'export_' . Str::limit(Str::snake(preg_replace('/[^a-zA-Z0-9]/', '', $entity->name)), 20) . '_' . Carbon::now()->format('YmdHis');
            $filepath = "";

            if(count($files) == 1) {
                $filename = $filename . '.csv';
                Storage::disk('private')->move(array_pop($files), $filename);
                $filepath = Storage::disk('private')->path($filename);
            } else {
                // Create a ZIP file
                $filename = $filename . '.zip';
                $zip = new ZipArchive;
                $filepath = Storage::disk('private')->path($filename);

                if($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                    foreach($files as $file) {
                        $zip->addFile(Storage::disk('private')->path($file), basename($file));
                    }
                    $zip->close();
                }
            }

            // Clean up the temporary files
            Storage::disk('private')->deleteDirectory($tmpDir);

            // Return the ZIP file as a download response
            return response()->download($filepath, $filename)->deleteFileAfterSend(true);
        } catch(Exception $e) {
            Storage::disk('private')->delete($tmpDir);
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => __('An unexpected error occurred while exporting the entities'),
            ], 500);
        }
    }

    private function createImportFilesForDistinctEntityTypes(array $entities, string $tmpDir): array {
        $files = [];
        $headersMap = [];
        // TODO handle in Entity with other metadata (e.g. creator, licence, …)
        $metadataFields = ['_name', '_entity_type', '_parent'];
        foreach($entities as $entity) {
            $entityTypeName = $entity['_entity_type'];
            $entityTypeId = $entity['_entity_type_id'];
            $delimiter = ";";

            if(!isset($files[$entityTypeId])) {
                $filename = $tmpDir . '/' . $entityTypeName . '-' . $entityTypeId . '_' . Carbon::now()->format('YmdHis') . '.csv';
                $files[$entityTypeId] = $filename;

                $entityType = EntityType::find($entityTypeId);
                $attributes = $entityType->attributes->all();

                // We use the id for the mapping to prevent conflicts from
                // attributes with the same name.
                $attributeIds = [];
                $attributeNames = [];
                $excludedAttributeTypes = ['system-separator'];
                foreach($attributes as $attribute) {
                    if(in_array($attribute->datatype, $excludedAttributeTypes)) {
                        continue;
                    }
                    $attributeIds[] = $attribute->id;
                    $attributeNames[] = $attribute->thesaurus_concept->getActiveLocaleLabel();
                }

                $mergedHeaderKeyMap = array_merge($attributeIds, $metadataFields);
                $headersMap[$entityTypeId] = $mergedHeaderKeyMap;
                $mergedHeaderNames = array_merge($attributeNames, $metadataFields);

                $headerStrings = array_map(function($header) {
                    $header = str_replace('"', '\"', $header);
                    $header = str_replace('\n', '', $header);
                    return '"' . $header . '"';
                }, $mergedHeaderNames);
                Storage::disk('private')->put($filename, implode($delimiter,  $headerStrings));
            }

            $filename = $files[$entityTypeId];
            $columns = [];
            $columnHeaders = $headersMap[$entityTypeId];
            foreach($columnHeaders as $columnHeader) {
                $columns[] = isset($entity[$columnHeader]) ? $entity[$columnHeader] : "";
            }
            Storage::disk('private')->append($filename, implode($delimiter, array_map(function($item) {
                return '"' . $item . '"';
            }, $columns)));
            $files[$entityTypeId] = $filename;
        }
        return $files;
    }

    function createImportedEntity($entityName, ?string $rootEntityPath, $entityTypeId, $user) {
        $rootEntityId = null;
        if(isset($rootEntityPath)) {
            try {
                $rootEntityId = Entity::getFromPath($rootEntityPath);
            } catch(AmbiguousValueException $ave) {
                throw new Exception($ave->getMessage());
            }
        }

        return Entity::create([
            'name' => $entityName,
        ], $entityTypeId, $user, $rootEntityId);
    }

    function setOrUpdateImportedAttributes($entity_id, $row, $headerRow, $attributeIdToColumnIdxMapping, $attributeTypes, $user) {
        foreach($attributeIdToColumnIdxMapping as $key => $colIdx) {
            $aid = intval($key);
            $type = $attributeTypes[$aid];

            $attrVal = AttributeValue::firstOrNew([
                'entity_id' => $entity_id,
                'attribute_id' => $key,
            ], [
                'user_id' => $user->id,
            ]);
            try{
                $setValue = $attrVal->setValueFromRaw($row[$colIdx], $type);
            } catch(InvalidDataException $e) {
                throw new AttributeImportException(
                    $e->getMessage(),
                    new AttributeImportExceptionStruct(
                        type: $type,
                        columnIndex: $colIdx + 1,
                        columnValue: $row[$colIdx],
                        columnName: $headerRow[$colIdx]
                    )
                );
            }

            if($setValue === null) {
                continue;
            }
            $attrVal->save();
        }
    }

    // PATCH

    public function patchAttributes($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data'),
            ], 403);
        }

        try{
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }

        DB::beginTransaction();
        $addedAttributes = [];
        $removedAttributes = [];

        if(count($request->request) === 0) {
            return response()->json([
                'entity' => $entity,
                'added_attributes' => $addedAttributes,
                'removed_attributes' => $removedAttributes,
            ], 204);
        }

        foreach($request->request as $patch) {
            $op = $patch['op'];
            $aid = $patch['params']['aid'];
            $error = null;
            switch($op) {
                case 'remove':
                    $attrval = AttributeValue::where([
                        ['entity_id', '=', $id],
                        ['attribute_id', '=', $aid],
                    ])->first();
                    if(!isset($attrval)) {
                        $error = __('This attribute value does either not exist or is in moderation state.');
                        break;
                    }
                    if($user->isModerated()) {
                        $attrval->moderate('pending-delete', true);
                    } else {
                        $removedAttributes[$aid] = $attrval;
                        $attrval->delete();
                    }
                    break;

                /**
                 * In the case when a user created the attribute, while another was visiting the
                 * page and sends an 'add' operation, and the other user also sends his changes,
                 * the application would have thrown an error, that the attribute was already created.
                 *
                 * That's why we combined the add and replace operations into one case.
                 * [SO] 29.01.2025
                 */
                case 'add':
                case 'replace':
                    $alreadyModerated = AttributeValue::where('entity_id', $id)
                        ->where('attribute_id', $aid)
                        ->onlyModerated()
                        ->exists();

                    // Currently the logic is that a moderated state cannot be changed
                    // by a moderated user.
                    if($alreadyModerated && $user->isModerated()) {
                        $error = __('This attribute value is in moderation state. A user with appropriate permissions has to accept or deny it first.');
                        break;
                    }
                    $value = $patch['value'];
                    $attrval = AttributeValue::firstOrNew([
                        'entity_id' => $id,
                        'attribute_id' => $aid,
                    ], [
                        'certainty' => null
                    ]);
                    if($user->isModerated()) {
                        $attrval = $attrval->moderate('pending', false, true);
                        unset($attrval->comments_count);
                    }
                    break;
                default:
                    $error = __('Unknown operation');
            }

            if($error !== null) {
                DB::rollBack();
                return response()->json([
                    'error' => $error,
                ], 400);
            }

            // no further action required for deleted attribute values, continue with next patch
            if($op == 'remove') {
                continue;
            }

            try {
                $attr = Attribute::findOrFail($aid);
                $formKeyValue = AttributeValue::getFormattedKeyValue($attr->datatype, $value);
            } catch(InvalidDataException $ide) {
                return response()->json([
                    'error' => $ide->getMessage(),
                ], 422);
            }

            $attrval->{$formKeyValue->key} = $formKeyValue->val;
            $attrval->user_id = $user->id;
            $attrval->save();

            // As we cannot ensure that the 'add' is correct,
            // we use this laravel option to ensure the attribute
            // was created and not replaced.
            if($attrval->wasRecentlyCreated) {
                $addedAttributes[$aid] = $attrval;
            }
        }

        // Save model if last editor changed
        // Only update timestamps otherwise
        $entity->user_id = $user->id;
        if($entity->isDirty()) {
            $entity->save();
        }else{
            $entity->touch();
        }

        DB::commit();
        $entity->load('user');

        return response()->json([
            'entity' => $entity,
            'added_attributes' => $addedAttributes,
            'removed_attributes' => $removedAttributes,
        ]);
    }

    public function patchAttribute($id, $aid, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data'),
            ], 403);
        }
        $this->validate($request, AttributeValue::patchRules);

        try{
            Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }
        try{
            Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This attribute does not exist'),
            ], 400);
        }

        $attrValue = AttributeValue::firstOrCreate([
            'entity_id' => $id,
            'attribute_id' => $aid,
        ], [
            'user_id' => $user->id,
        ]);
        // When attribute value already exists and nothing changed
        // (same certainty)
        if(
            !$attrValue->wasRecentlyCreated
            &&
            ($request->has('certainty') && $request->get('certainty') == $attrValue->certainty)
        ) {
            return response()->json($attrValue);
        }
        $attrValue->user_id = $user->id;
        $values = $request->only(array_keys(AttributeValue::patchRules));
        $attrValue->patch($values);

        return response()->json($attrValue, 201);
    }

    public function multieditAttributes(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data'),
            ], 403);
        }

        $this->validate($request, [
            'entity_ids' => 'required|array',
            'entries' => 'required|array',
        ]);

        $entities = $request->get('entity_ids');
        $attrValues = $request->get('entries');

        DB::beginTransaction();

        foreach($attrValues as $av) {
            try {
                $attr = Attribute::findOrFail($av['attribute_id']);
            } catch(ModelNotFoundException $e) {
                DB::rollBack();
                return response()->json([
                    'error' => __('This attribute does not exist'),
                ], 400);
            }
            try{
                $formKeyValue = AttributeValue::getFormattedKeyValue($attr->datatype, $av['value']);
            } catch(InvalidDataException $ide) {
                DB::rollBack();
                return response()->json([
                    'error' => $ide->getMessage(),
                ], 422);
            }
            foreach($entities as $eid) {
                AttributeValue::updateOrCreate(
                    ['entity_id' => $eid, 'attribute_id' => $av['attribute_id']],
                    [
                        $formKeyValue->key => $formKeyValue->val,
                        'user_id' => $user->id,
                    ]
                );
            }
        }

        DB::commit();

        return response()->json(null, 204);
    }

    public function handleModeration($id, $aid, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_data_write') || $user->isModerated()) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data'),
            ], 403);
        }
        $this->validate($request, [
            'action' => 'required|string|mod_action',
            'value' => 'nullable',
        ]);

        $action = $request->get('action');

        try{
            Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }
        try{
            $attribute = Attribute::findOrFail($aid);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This attribute does not exist'),
            ], 400);
        }

        $attrValue = AttributeValue::where('entity_id', $id)
            ->where('attribute_id', $aid)
            ->onlyModerated()
            ->first();

        if(!isset($attrValue)) {
            return response()->json([
                'error' => __('This attribute value does not exist'),
            ], 400);
        }

        $attrValue->moderate($action);

        $editedValue = $request->get('value');
        if(isset($editedValue) && $action == 'accept') {
            try {
                $formKeyValue = AttributeValue::getFormattedKeyValue($attribute->datatype, $editedValue);
            } catch(InvalidDataException $ide) {
                return response()->json([
                    'error' => $ide->getMessage(),
                ], 422);
            }
            $attrValue->{$formKeyValue->key} = $formKeyValue->val;
            $attrValue->user_id = $user->id;
            $attrValue->save();
        }

        return response()->json(null, 204);
    }

    public function patchName($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s data'),
            ], 403);
        }
        $this->validate($request, [
            'name' => 'required|string',
        ]);

        try{
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }

        $entity->name = $request->get('name');
        $entity->user_id = $user->id;

        $entity->save();

        $entity->load('user');

        return response()->json($entity);
    }

    public function patchMetadata($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity\'s metadata')
            ], 403);
        }
        $fields = $this->validate($request, [
            'licence' => 'nullable|string',
            'summary' => 'nullable|string',
        ]);

        try {
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist')
            ], 400);
        }

        $metadata = $entity->metadata;
        foreach($fields as $field => $value) {
            $metadata[$field] = $value;
        }
        $entity->metadata = $metadata;
        $entity->save();

        return response()->json($entity->getAllMetadata());
    }

    public function moveEntity(Request $request, $id) {
        $user = auth()->user();
        if(!$user->can('entity_write')) {
            return response()->json([
                'error' => __('You do not have the permission to modify an entity'),
            ], 403);
        }
        $this->validate($request, [
            'rank' => 'required|integer',
            'parent_id' => 'nullable|integer|exists:entities,id',
            'to_end' => 'boolean',
        ]);

        try{
            Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }

        $rank = $request->get('rank');
        $parent_id = $request->get('parent_id');
        $addToEnd = $request->get('to_end');

        if($addToEnd) {
            if(isset($parent_id)) {
                $rank = Entity::where('root_entity_id', $parent_id)->max('rank') + 1;
            }else{
                $rank = Entity::whereNull('root_entity_id')->max('rank') + 1;
            }
        }

        try{
            Entity::patchRanks($rank, $id, $parent_id, $user);
        }catch(Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }

        return response()->json(null, 204);
    }

    // DELETE

    public function deleteEntity($id) {
        $user = auth()->user();
        if(!$user->can('entity_delete')) {
            return response()->json([
                'error' => __('You do not have the permission to delete an entity'),
            ], 403);
        }
        try{
            $entity = Entity::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This entity does not exist'),
            ], 400);
        }
        $entity->delete();

        return response()->json(null, 204);
    }
}
