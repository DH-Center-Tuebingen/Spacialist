<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\Bibliography;
use App\Entity;
use App\EntityType;
use App\Plugins\File\App\File;
use App\Plugins\Map\App\SearchAspect\GeodataAspect;
use App\SearchAspect\AttributeValueAspect;
use App\ThConcept;
use App\ThLanguage;

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

    private function stripShebang(string $query) : string {
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

    private function noShebang(string $query) : bool {
        return !Str::startsWith($query, '!') || !($this->isBib($query) || $this->isEntity($query) || $this->isFile($query) || $this->isGeodata($query));
    }

    private function isBib(string $query) : bool {
        return Str::startsWith($query, self::$shebangPrefix['bibliography']);
    }

    private function isEntity(string $query) : bool {
        return Str::startsWith($query, self::$shebangPrefix['entities']);
    }

    // TODO handle in Plugin
    private function isFile($query) {
        return Str::startsWith($query, self::$shebangPrefix['files']);
    }

    // TODO handle in Plugin
    private function isGeodata($query) {
        return Str::startsWith($query, self::$shebangPrefix['geodata']);
    }

    public function searchGlobal(Request $request) {
        $user = auth()->user();

        // Search is currently supported in
        // Entity, AttributeValue, Bibliography and
        // Following Plugins: File, Geodata (TODO handle in Plugins or a Search Handler)
        if(
            !$user->can('bibliography_read') &&
            !$user->can('entity_read') &&
            !$user->can('entity_data_read') &&
            !$user->can('file_read') &&
            !$user->can('geodata_read')
        ) {
            return response()->json([
                'error' => __('You do not have the permission to search global')
            ], 403);
        }

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

        $matches = $builder
            ->with('broaders')
            ->whereHas('labels', function($query) use ($langId, $q) {
                $query->where('language_id', $langId)
                    ->where('label', 'ilike', "%$q%");
            })
            ->get();

        $foreignMatches = th_tree_builder($lang)
            ->with('broaders')
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

    public function getConceptChildren(int $id, Request $request) {
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
