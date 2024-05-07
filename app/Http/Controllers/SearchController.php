<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\Bibliography;
use App\Entity;
use App\EntityType;
use App\Group;
use App\Plugins\File\App\File;
use App\Plugins\Map\App\SearchAspect\GeodataAspect;
use App\SearchAspect\AttributeValueAspect;
use App\ThConcept;
use App\ThLanguage;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Spatie\Searchable\Search;

class SearchController extends Controller {
    private static $shebangPrefix = [
        'bibliography' => '!b ',
        'entities' => '!e ',
        'files' => '!f ',
        'geodata' => '!g ',
    ];

    private function stripShebang($query) {
        if($this->isBib($query)) {
            return Str::after($query, self::$shebangPrefix['bibliography']);
        } else if($this->isEntity($query)) {
            return Str::after($query, self::$shebangPrefix['entities']);
        } else if($this->isFile($query)) {
            return Str::after($query, self::$shebangPrefix['files']);
        } else if($this->isGeodata($query)) {
            return Str::after($query, self::$shebangPrefix['geodata']);
        } else {
            return $query;
        }
    }

    private function noShebang($query) {
        return !Str::startsWith($query, '!') || !($this->isBib($query) || $this->isEntity($query) || $this->isFile($query) || $this->isGeodata($query));
    }

    private function isBib($query) {
        return Str::startsWith($query, self::$shebangPrefix['bibliography']);
    }

    private function isEntity($query) {
        return Str::startsWith($query, self::$shebangPrefix['entities']);
    }

    private function isFile($query) {
        return Str::startsWith($query, self::$shebangPrefix['files']);
    }

    private function isGeodata($query) {
        return Str::startsWith($query, self::$shebangPrefix['geodata']);
    }

    public function searchGlobal(Request $request) {
        $user = auth()->user();
        $q = $request->query('q');
        $stripedQuery = $this->stripShebang($q);
        $search = new Search();
        if(($this->noShebang($q) || $this->isBib($q)) && $user->can('bibliography_read')) {
            $search = $search->registerModel(Bibliography::class, Bibliography::getSearchCols());
        }
        if(($this->noShebang($q) || $this->isEntity($q)) && ($user->can('entity_read') && $user->can('entity_data_read'))) {
            $search = $search->registerModel(Entity::class, Entity::getSearchCols());
            $search = $search->registerAspect(AttributeValueAspect::class);
        }
        if(($this->noShebang($q) || $this->isFile($q)) && $user->can('file_read') ) {
            $search = $search->registerModel(File::class, File::getSearchCols());
        }
        if(($this->noShebang($q) || $this->isGeodata($q)) && $user->can('geodata_read')) {
            $search = $search->registerAspect(GeodataAspect::class);
        }
        $matches = $search->search($stripedQuery);
        return response()->json($matches);
    }

    public function searchEntityByName(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_read')) {
            return response()->json([
                'error' => __('You do not have the permission to search for entities')
            ], 403);
        }
        $q = $request->query('q');
        $t = $request->query('t');

        $matches = Entity::where('name', 'ilike', '%'.$q.'%');
        if(isset($t)) {
            $types = explode(',', $t);
            $matches->whereIn('entity_type_id', $types);
        }
        $matches = $matches
            ->orderBy('name')
            ->get();
        $matches->each->append(['ancestors']);
        return response()->json($matches);
    }

    public function searchEntityTypes(Request $request) {
        $user = auth()->user();
        if(!$user->can('entity_type_read')) {
            return response()->json([
                'error' => __('You do not have the permission to search for entities')
            ], 403);
        }
        $q = $request->query('q');
        $lang = $user->getLanguage();

        try {
            $language = ThLanguage::where('short_name', $lang)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Your language does not exist in ThesauRex'
            ], 400);
        }

        $langId = $language->id;
        $matches = EntityType::with(['thesaurus_concept'])
            ->whereHas('thesaurus_concept.labels', function($query) use ($langId, $q) {
                $query->where('language_id', $langId)
                    ->where('label', 'ilike', "%$q%");
            })
            ->get();
        return response()->json($matches);
    }

    public function searchInThesaurus(Request $request) {
        $user = auth()->user();
        if(!$user->can('thesaurus_read')) {
            return response()->json([
                'error' => __('You do not have the permission to search for concepts')
            ], 403);
        }
        $q = $request->query('q');
        $lang = $user->getLanguage();

        try {
            $language = ThLanguage::where('short_name', $lang)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Your language does not exist in ThesauRex'
            ], 400);
        }

        $langId = $language->id;
        $builder = th_tree_builder($lang);

        $matches = $builder->whereHas('labels', function($query) use ($langId, $q){
            $query->where('language_id', $langId)
                ->where('label', 'ilike', "%$q%");
        })
            ->get();

        $foreignMatches = th_tree_builder($lang)
            ->whereDoesntHave('labels', function($query) use ($langId) {
                $query->where('language_id', $langId);
            })
            ->whereHas('labels', function($query) use ($q) {
                $query->where('label', 'ilike', "%$q%");
            })
            ->get();

        $matches = $matches->merge($foreignMatches);
        return response()->json($matches);
    }

    public function searchInAttributes(Request $request) {
        $user = auth()->user();
        if(!$user->can('thesaurus_read') || !$user->can('attribute_read')) {
            return response()->json([
                'error' => __('You do not have the permission to search for attributes')
            ], 403);
        }

        $q = $request->query('q');
        $lang = auth()->user()->getLanguage();
        $langId = ThLanguage::where('short_name', $lang)->value('id');
        $matches = Attribute::where('datatype', 'string-sc')
            ->whereHas('thesaurus_concept.labels', function($query) use($q, $langId) {
                $query->where('label', 'ilike', "%$q%")
                    ->where('language_id', $langId);
            })
            ->with('thesaurus_concept')
            ->get();

        $foreignMatches = Attribute::where('datatype', 'string-sc')
            ->whereDoesntHave('thesaurus_concept.labels', function($query) use ($langId) {
                $query->where('language_id', $langId);
            })
            ->whereHas('thesaurus_concept.labels', function($query) use ($q) {
                $query->where('label', 'ilike', "%$q%");
            })
            ->with('thesaurus_concept')
            ->get();

        $matches = $matches->merge($foreignMatches);
        return response()->json($matches);
    }

    public function searchInUsersAndGroups(Request $request) {
        $user = auth()->user();
        // if(!$user->can('users_roles_read') && !$user->can('working_groups_read')) {
        if(!$user->can('users_roles_read')) {
            return response()->json([
                'error' => __('You do not have the permission to search for users and working groups')
            ], 403);
        }

        
        $q = $request->query('q');
        $userMatches = User::withoutTrashed()
            ->where(function($query) use ($q) {
                $query->where('name', 'ilike', "%$q%")
                    ->orWhere('nickname', 'ilike', "%$q%")
                    ->orWhere('email', 'ilike', "%$q%")
                    ->orWhereJsonContains('metadata->orcid', "%$q%");
            })
            ->get()
            ->map(function($u) {
                $u->result_type = 'u';
                return $u;
        });
        
        $groupMatches = Group::where('name', 'ilike', "%$q%")
            ->orWhere('display_name', 'ilike', "%$q%")
            ->orWhere('description', 'ilike', "%$q%")
            ->get()
            ->map(function($g) {
                $g->result_type = 'wg';
                return $g;
            });

        $matches = $userMatches->concat($groupMatches);

        return response()->json($matches);
    }

    public function getConceptChildren($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('thesaurus_read')) {
            return response()->json([
                'error' => __('You do not have the permission to search for concepts')
            ], 403);
        }

        try {
            $concept = ThConcept::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This concept does not exist')
            ], 400);
        }

        return response()->json(ThConcept::getChildren($concept->concept_url, false));
    }
}
