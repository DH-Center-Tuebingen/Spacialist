<?php

namespace App\Console\Commands;

use App\Entity;
use App\EntityType;
use App\ThConcept;
use App\Attribute;
use App\Console\Utils\Printer;
use App\EntityAttribute;
use App\ThBroader;
use App\ThConceptLabel;
use App\ThLanguage;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


/**
 * Caveats:
 *      - Attributes: Attributes are assumed to have the same thesaurus_url and datatype. This may lead to problems if the same attribute is used in different contexts.
 */
class ExportEntity extends Command {
    
    use Printer;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export {--e|entity=: The id of the entity to export}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "cli tool to export a specific and all it's dependencies";

    private static $source = "pgsql";
    private static $destination = "transfer";
    private $labels = [];
    private $languageMap = [];
    private $thConceptOldIdToIdMap = [];
    private $entityTypeOldIdToIdMap = [];
    private $attributeOldIdToIdMap = [
        1 => 1,
    ];
    
    
    protected function printerLogFileName(){
        return "transfer.log";
    }
    
    private function extractThesaurusConcepts($model, array &$allConcepts){
        $collection = ThConcept::where('concept_url', $model->thesaurus_url)->get();
        foreach($collection as $concept){
                $allConcepts[$model->thesaurus_url] = $concept;
        }        
    }
    
    private function getLabel($model){
        $collection = ThConcept::where('concept_url', $model->thesaurus_url)->firstOrFail();
        return $collection->labels->first()->label;
    }
    
    private function collectAllDataFromEntityChildren(Entity $entity){
        $thesaurusLabels = [];
        $entityTypeIds = $this->getEntityTypes($entity);
        $entityTypes = array_map(function($entityType){
            $entityType = EntityType::findOrFail($entityType);
            return $entityType;
        }, $entityTypeIds);
        $thesaurusConcepts = [];
        $attributes = [];
        $entityTypeAttributeRelations = [];
        foreach($entityTypes as $entityType){
            $this->extractThesaurusConcepts($entityType, $thesaurusConcepts);
            foreach($entityType->attributes as $attribute){
                $fetchedAttribute = Attribute::findOrFail($attribute->id);
                
                if($attribute->id === 1){
                    $entityAttribute = EntityAttribute::where('entity_type_id', $entityType->id)
                    ->where('attribute_id', $fetchedAttribute->id)
                    ->whereJsonContains('metadata', ['title' => $attribute->pivot->metadata["title"]])
                    ->first();
                }else {
                    $entityAttribute = EntityAttribute::where('entity_type_id', $entityType->id)
                    ->where('attribute_id', $fetchedAttribute->id)
                    ->first();
                }
                
                $entityTypeAttributeRelations[] = $entityAttribute;
                if($fetchedAttribute->datatype === "system-separator"){
                    continue;
                }

                $attributes[$fetchedAttribute->id] = $fetchedAttribute;
                $this->extractThesaurusConcepts($attribute, $thesaurusConcepts);
            }
        }

        $validLanguages = ['de', 'en'];
        foreach($thesaurusConcepts as $concept){
            $concept->labels->each(function($label) use ($validLanguages, &$thesaurusLabels, $concept){
                if(in_array($label->language->short_name, $validLanguages)){
                    $lang = $label->language->short_name;
                    $thesaurusLabels[$concept->concept_url . "-" . $lang] = $label;
                }
            });
        }
        
        return [
            'attributes' => $attributes,
            'entityTypes' => $entityTypes,
            'thesaurusConcepts' => $thesaurusConcepts,
            'thesaurusLabels' => $thesaurusLabels,
            'entityTypeAttributeRelations' => $entityTypeAttributeRelations,
        ];
    }
    
    public function handle(){
        try{
            DB::connection(static::$source)->getPdo();
        }catch(\Exception $e){
            $this->print("Could not connect to '" . static::$source . "' database " . $e->getMessage());
            return 1;
        }

        try{
            DB::connection(static::$destination)->getPdo();
        }catch(\Exception $e){
            $this->print("Could not connect to'". static::$destination . "' database " . $e->getMessage());
            return 1;
        }

        $this->startPrinter();
        try{
            $entityId = $this->option('entity');
            
            $entity = Entity::find($entityId);
            if(!$entity){
                $this->print('Entity not found');
                return 1;
            }
            
            $this->print("Analyze structure starting at base entity: " . $entity->name . " (" . $entity->id . ")");
            $this->printSectionHeading("Traverse Entity Tree & Extracting Data Models");
            
            $data = $this->collectAllDataFromEntityChildren($entity);
            
            $this->printReceipt($data);
            $this->print("Beginning Transaction");
            
            DB::connection("transfer")->beginTransaction(); 
            $user = $this->selectOrCreateTransferUser();
            $this->transferData($user, $data);

            $this->print("Waiting for user confirmation of this receipt ...");
            if($this->confirm('Check the receipt carefully before proceeding')){
                $this->print("User confirmed receipt. Proceeding with commit");
                DB::connection("transfer")->commit();
            }else{
                $this->print("User declined receipt. Rolling back transaction and exiting Program");
                DB::connection("transfer")->rollBack();
                return 1;
            }
        
        //    // We need to commit the changes first before we can (re-)build the thesaurus tree
        //    $allConceptsInTree = $this->createMissingThConcepts($user, $data['thesaurusConcepts']);
        //    $this->linkBroaderThConcepts($user, $allConceptsInTree);
        
       
        } catch(Exception $e) {
            $this->print("The program failed unexpectedly:\n" . $e->getMessage() . "\n" . $e->getTraceAsString());
            return 1;
        } finally {
            $this->closePrinter();
        }
       
       ////////// LEGACY
       
        // $user = User::on('transfer')->firstOrFail();
        
        // try{
        //     foreach($thesaurusConcepts as $concept){
        //         try{
        //             fwrite($filehandle,"[[INSERT]] " . $concept::class . ": " . $concept->id . "\n");
        //             $clone = $concept->replicate();
        //             $clone->user_id = $user->id;
        //             $clone->setConnection("transfer");
        //             $clone->save();
        //         }catch(\Exception $e){
        //             fwrite($filehandle,"[[ERROR]] " . $e->getMessage(). "\n");
        //             fwrite($filehandle,"[[ROLLBACK]] Transaction On Transfer " . $concept->id . "\n");
        //             DB::connection("transfer")->rollBack();
        //             return 1;
        //         }
        //     }
            
        //     fwrite($filehandle,"[[COMMIT]] Transaction On Transfer " . $concept->id . "\n");
        //     DB::connection("transfer")->commit();
        // }catch(\Exception $e){
        //     fwrite($filehandle,"[[ERROR]] " . $e->getMessage(). "\n");
        //     fwrite($filehandle,"[[ROLLBACK]] Transaction On Transfer " . $concept->id . "\n");
        //     DB::connection("transfer")->rollBack();
        //     return 1;
        // }finally{
        //     fclose($filehandle);
        // }
    }
    
    private function transferData($user, $data){
        $this->printSectionHeading("Transfering Data Models to Transfer Database");
        $this->printHeading("Transfering Thesaurus Concepts", $data['thesaurusConcepts']);
        $this->transferThConcepts($user, $data['thesaurusConcepts']);
        $this->printSeparator();
        $this->printHeading("Transfering Thesaurus Labels", $data['thesaurusLabels']);
        $this->transferThLabels($user, $data['thesaurusLabels']);
        $this->printSeparator();
        $this->printHeading("Transfering Entity Types", $data['entityTypes']);
        $this->transferEntityTypes($user, $data['entityTypes']);
        $this->printSeparator();
        $this->printHeading("Transfering Attributes", $data['attributes']);
        $this->transferAttributes($user, $data['attributes']);
        $this->printSeparator();
        $this->printHeading("Transfering Entity Type Attributes Relation", $data['entityTypeAttributeRelations']);
        $this->transferEntityTypeAttributeRelations($user, $data['entityTypeAttributeRelations']);
        $this->printSeparator();
    }
    
    private function selectOrCreateTransferUser(): User{
        $user = User::on(static::$destination)->where('nickname', '__transfer')->firstOrCreate(
            [
                'nickname' => '__transfer',
            ],[
                'name' => 'Transfer User',
                'email' => 'nomail@localhost',
                'password' => bcrypt('__transfer'),
                'deleted_at' => Carbon::now(),
            ]
        );
        
        return $user;
    }

    private function printReceipt($data){
        $languages = ThLanguage::all()->toArray();
        foreach($languages as $language){
            $this->languageMap[$language['id']] = $language;
        }

        $this->printSeparator();
        $this->printSectionHeading("Data Collection Receipt");
        $this->printHeading("Attributes");
        foreach($data['attributes'] as $key => $value){
             $label = ThConcept::getLabel($value->thesaurus_url);
             $this->labels[$value->thesaurus_url] = $label;
             $this->printInfo($label . " (" . $value->id . ")");
        }
        $this->printSeparator();
        $this->printHeading("Entity Types");
        foreach($data['entityTypes'] as $key => $value){
            $label = ThConcept::getLabel($value->thesaurus_url);
            $this->labels[$value->thesaurus_url] = $label;
            $this->printInfo($label . " (" . $value->id . ")");
        }
        $this->printSeparator();
        $this->printHeading("Thesaurus Concepts");
        foreach($data['thesaurusConcepts'] as $key => $value){
            $label = ThConcept::getLabel($value->concept_url);
            $this->labels[$value->concept_url] = $label;
            $this->printInfo($label . " (" . $value->id . ") => " . $value->concept_url);
        }
        $this->printSeparator();
        $this->printHeading("Thesaurus Labels");
        foreach($data['thesaurusLabels'] as $key => $value){
            $label = $value->label;
            $langShort = $this->languageMap[$value->language_id]["short_name"];
            $this->printInfo("[$langShort of ". $value->concept_id."] " . $label . " (" . $value->id . ")");
        }
        $this->printSeparator();
    } 

    private function transferThConcepts(User $user, array $data){
        foreach($data as $concept){
            $result = ThConcept::on("transfer")->where('concept_url', $concept->concept_url)->first();
            $message = " >> ThConcept for " . $this->labels[$concept->concept_url];
            if(!$result){
                $clone = $concept->replicate();
                $clone->user_id = $user->id;
                $clone->setConnection("transfer");
                $clone->save();
                $this->thConceptOldIdToIdMap[$concept->id] = $clone->id;
                $this->printCreated($message);
            }else{
                $this->thConceptOldIdToIdMap[$concept->id] = $result->id;
                $this->printExists($message);
            }
        }
    }
    
    private function transferThLabels(User $user, array $thLabels){
        foreach($thLabels as $label){
            $newConceptId = $this->thConceptOldIdToIdMap[$label->concept_id];
            $result = ThConceptLabel::on("transfer")->where('concept_id', $newConceptId)->where('language_id', $label->language_id)->first();
            $message = " >> ThConceptLabel for " . $label->concept_id . "(-> $newConceptId) in " . $this->languageMap[$label->language_id]['short_name'];
            if(!$result){
                $clone = $label->replicate();
                $clone->user_id = $user->id;
                $clone->concept_id = $this->thConceptOldIdToIdMap[$clone->concept_id];
                $clone->setConnection("transfer");
                $clone->save();
                $this->printCreated( $message);
            }else{
                $this->printExists($message);
            }
        }
    }
    
    private function transferEntityTypes(User $user, array $entityTypes){
        foreach($entityTypes as $entityType){
            $result = EntityType::on("transfer")->where('thesaurus_url', $entityType->thesaurus_url)->first();
            $message = " >> " . $this->labels[$entityType->thesaurus_url];
            if(!$result){
                $clone = $entityType->replicate();
                $clone->setConnection("transfer");
                $clone->save();
                $this->entityTypeOldIdToIdMap[$entityType->id] = $clone->id;
                $this->printCreated($message);
            }else{
                $this->entityTypeOldIdToIdMap[$entityType->id] = $result->id;
                $this->printExists($message);
            }
        }
    }
    
    private function transferAttributes(User $user, array $attributes){
        foreach($attributes as $attribute){
            $result = Attribute::on("transfer")->
                where('thesaurus_url', $attribute->thesaurus_url)->
                where('datatype', $attribute->datatype)->first();
                
            $message = " >> " . $this->labels[$attribute->thesaurus_url] . " (" . $attribute->datatype . ")";
            if(!$result){
                $clone = $attribute->replicate();
                $clone->setConnection("transfer");
                $clone->save();
                $this->attributeOldIdToIdMap[$attribute->id] = $clone->id;
                $this->printCreated($message);
            }else{
                $this->attributeOldIdToIdMap[$attribute->id] = $result->id;
                $this->printExists($message);
            }
        }
    }
    
    private function transferEntityTypeAttributeRelations(User $user, array $entityTypeAttributes){
        foreach($entityTypeAttributes as $entityTypeAttribute){
            $attributeId = $entityTypeAttribute->attribute_id;
            $entityTypeId = $entityTypeAttribute->entity_type_id;
            $err = false;
            if(!isset($this->attributeOldIdToIdMap[$attributeId])){
                $this->print("Attribute ID not found in map: " . $attributeId);
                $err = true;
            }
            if(!isset($this->entityTypeOldIdToIdMap[$entityTypeId])){
                $this->print("Entity Type ID not found in map: " . $entityTypeId);
                $err = true;
            }
            
            if($err) continue;
            
            $newAttributeId = $this->attributeOldIdToIdMap[$attributeId];
            $newEntityTypeId = $this->entityTypeOldIdToIdMap[$entityTypeId];
            
            $result = EntityAttribute::on("transfer")->
                where('entity_type_id', $newEntityTypeId)->
                where('attribute_id', $newAttributeId)->
                where('metadata', $entityTypeAttribute->metadata)->first();
            
            $message = " >> from [e:$entityTypeId <- a:$attributeId] to [e:$newEntityTypeId <- a:$newAttributeId]";
            
            if($entityTypeAttribute->metadata && $entityTypeAttribute->metadata != ""){
                $message .= " +++ metadata: " . $entityTypeAttribute->metadata;
            }
            if(!$result){
                $clone = $entityTypeAttribute->replicate();
                $clone->entity_type_id = $newEntityTypeId;
                $clone->attribute_id = $newAttributeId;
                $clone->setConnection("transfer");
                $clone->save();
                $this->printCreated($message);
            }else{
                $this->printExists($message);
            }
        }
    }
    
    private function createMissingThConcepts(User $user, array $concepts){
        $conceptsInTree = array_values($concepts);
        
        $idx = 0;
        while(count($conceptsInTree) > $idx){
            $concept = $conceptsInTree[$idx];
            $originalId = ThConcept::where('concept_url', $concept->concept_url)->value('id');
            $broaders = ThBroader::where('narrower_id', $originalId)->get();
            foreach($broaders as $broader){
                $broaderId = $broader->broader_id;
                $broaderConcept = ThConcept::where('id', $broaderId)->first();
                $broaderConceptUrl = $broaderConcept->concept_url;
                
                $transferBroader = ThConcept::on("transfer")->where('concept_url', $broaderConceptUrl)->first();
                if(!$transferBroader){
                    $this->print("ThConcept " . $broaderConcept->id . " not found in transfer database. Creating it now.");
                    $concepts[$broaderConceptUrl] = $broaderConcept;
                    $transferBroader = ThConcept::where('concept_url', $broaderConceptUrl)->first();
                    $clone = $transferBroader->replicate();
                    $clone->user_id = $user->id;
                    $clone->setConnection("transfer");
                    $clone->save();
                }
            }
            $idx++;
        }
        return $concepts;
    }
    
    private function linkBroaderThConcepts(User $user, array $concepts){
        foreach($concepts as $concept){
            $originalId = ThConcept::where('concept_url', $concept->concept_url)->value('id');
            $broaders = ThBroader::where('narrower_id', $originalId)->get();
            $transferNarrowerId = ThConcept::on("transfer")->where('concept_url', $concept->concept_url)->value('id');
            foreach($broaders as $broader){
                $broaderId = $broader->broader_id;
                $broaderConcept = ThConcept::where('id', $broaderId)->first();
                $broaderConceptUrl = $broaderConcept->concept_url;
                
                $transferBroaderId = ThConcept::on("transfer")->where('concept_url', $broaderConceptUrl)->value('id');
                if(!$transferBroaderId){
                    throw new \Exception("Broader Concept not found in transfer database");
                }
                
                $broader = ThBroader::on("transfer")->where('narrower_id', $transferNarrowerId)->where('broader_id', $transferBroaderId)->first();
                if($broader){
                    $this->print("Broader link already exists:: " . $transferBroaderId . " >>> " . $transferNarrowerId);
                }else{
                    $this->print("Creating Broader link:: " . $transferBroaderId . " >>> " . $transferNarrowerId);
                    $broader = new ThBroader();
                    $broader->narrower_id = $transferNarrowerId;
                    $broader->broader_id = $transferBroaderId;
                    $broader->setConnection("transfer");
                    $broader->save();
                }
            }
        }
    }
    
    private function ellipsis($string, $max = 50){
        if(strlen($string) > $max){
            return substr($string, 0, $max) . "…";
        }
        return $string;
    }
    
    private function exactOrEllipsis($string, $max = 50){
        if(strlen($string) > $max){
            return substr($string, 0, $max-1) . "…";
        }
        return str_pad($string, $max, " ", STR_PAD_RIGHT);
    }
    
    public function getEntityTypes(Entity $entity, string $path = "") : array{
        $arr = [$entity->entity_type_id];
        $children = Entity::getEntitiesByParent($entity->id);
        if(count($children) == 0){
            return $arr;
        }

        $entityType = EntityType::findOrFail($entity->entity_type_id);
        
        $entityTypeName = "No Label";
        $entityTypeLabel = ThConcept::where('concept_url', $entityType->thesaurus_url)->first();

        if($entityTypeLabel){
            $entityTypeName = $entityTypeLabel->labels->first()->label;
        }

        $entityTypeName = $this->exactOrEllipsis($entityTypeName, 14);
        $entityName = $this->exactOrEllipsis($entity->name, 14);
        
        $this->printInfo("[$entityTypeName] $entityName (" . $entity->id . ") @" . $path);
        foreach($children as $child){
            $childEntityIds = $this->getEntityTypes($child, $path . "/" . $this->ellipsis($child->name, 14));
            foreach($childEntityIds as $childEntityId){
                $arr[] = $childEntityId;
            }
        }

        $entityTypeIdList = array_unique($arr);        
        return $entityTypeIdList;
    }
}