<?php

namespace App\Console\Commands;

use App\Entity;
use App\EntityType;
use App\ThConcept;
use App\Attribute;
use App\ThBroader;
use App\ThConceptLabel;
use App\ThLanguage;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportEntity extends Command {
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


    private $filehandle;
    private static $source = "pgsql";
    private static $destination = "transfer";
    private $labels = [];
    private $languageMap = [];
    
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
        foreach($entityTypes as $entityType){
            $this->extractThesaurusConcepts($entityType, $thesaurusConcepts);
            
            foreach($entityType->attributes as $attribute){
                $fetchedAttribute = Attribute::findOrFail($attribute->id);
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
            'thesaurusLabels' => $thesaurusLabels
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


        try{
            $logLocation = "./storage/logs/transfer.log";
            $this->filehandle = fopen($logLocation, "w");
            
            $entityId = $this->option('entity');
            
            $entity = Entity::find($entityId);
            if(!$entity){
                $this->print('Entity not found');
                return 1;
            }
            
            $this->print("Analyze structure starting at base entity: " . $entity->name . " (" . $entity->id . ")");
            
            $this->print();
            $this->print("=============================================================");
            $this->print("====== Traverse Entity Tree & Extracting Data Models =======");
            $this->print("=============================================================");
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
        $this->print("=============================================================");
        $this->print("======= Transfering Data Models to Transfer Database =======");
        $this->print("=============================================================");
        $this->print();
        $this->print("============ Transfering Thesaurus Concepts ================");
        $this->transferThConcepts($user, $data['thesaurusConcepts']);
        $this->print("=============================================================");
        $this->print();
        $this->print("============ Transfering Thesaurus Labels ==================");
        $this->transferThLabels($user, $data['thesaurusLabels']);
        $this->print("=============================================================");
        // $this->transferEntityTypes($user, $data['entityTypes']);
        // $this->transferAttributes($user, $data['attributes']);
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
    
    private function print($message = ""){
        fwrite($this->filehandle, Carbon::now() . " ::> " . $message . "\n");
    }

    private function printReceipt($data){
        $languages = ThLanguage::all()->toArray();
        foreach($languages as $language){
            $this->languageMap[$language['id']] = $language;
        }

        $this->print("=============================================================");
        $this->print();
        $this->print();
        $this->print("=============================================================");
        $this->print("===========         Collected Data Overview         =========");
        $this->print("=============================================================");
        $this->print();
        $this->print("========================= Attributes =========================");
        foreach($data['attributes'] as $key => $value){
             $label = ThConcept::getLabel($value->thesaurus_url);
             $this->labels[$value->thesaurus_url] = $label;
             $this->print($label . " (" . $value->id . ")");
        }
        $this->print("=============================================================");
        $this->print();
        $this->print("========================= Entity Types =========================");
        foreach($data['entityTypes'] as $key => $value){
            $label = ThConcept::getLabel($value->thesaurus_url);
            $this->labels[$value->thesaurus_url] = $label;
            $this->print($label . " (" . $value->id . ")");
        }
        $this->print("=============================================================");
        $this->print();
        $this->print("========================= Thesaurus Concepts =========================");
        foreach($data['thesaurusConcepts'] as $key => $value){
            $label = ThConcept::getLabel($value->concept_url);
            $this->labels[$value->concept_url] = $label;
            $this->print($label . " (" . $value->id . ") => " . $value->concept_url);
        }
        $this->print("=============================================================");
        $this->print();
        $this->print("========================= Thesaurus Labels =========================");
        foreach($data['thesaurusLabels'] as $key => $value){
            $label = $value->label;
            $langShort = $this->languageMap[$value->language_id]["short_name"];
            $this->print("[$langShort of ". $value->concept_id."] " . $label . " (" . $value->id . ")");
        }
        $this->print("=============================================================");
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
                $clone->save("CREATE" . $message);
            }else{
                $this->print("EXIST" . $message);
            }
        }
    }
    
    private function transferThLabels(User $user, array $data){
        foreach($data as $label){
            $result = ThConceptLabel::on("transfer")->where('concept_id', $label->concept_id)->where('language_id', $label->language_id)->first();
            $message = " >> ThConceptLabel for " . $label->concept_id . " in " . $this->languageMap[$label->language_id]['short_name'];
            if(!$result){
                $clone = $label->replicate();
                $clone->user_id = $user->id;
                $clone->setConnection("transfer");
                $clone->save("CREATE" . $message);
            }else{
                $this->print("EXIST" . $message);
            }
        }
    }
    
    private function transferEntityTypes(User $user, array $data){
        
    }
    
    private function transferAttributes(User $user, array $data){
        
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
            return substr($string, 0, $max) . "...";
        }
        return $string;
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

        $this->print($this->ellipsis($entity->name,14) . "(". $this->ellipsis($entityTypeName, 14) . ", " . $entityType->id . ")" . " (" . $entity->id . ") @" . $path);
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