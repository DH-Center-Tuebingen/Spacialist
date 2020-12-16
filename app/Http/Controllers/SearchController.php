<?php

namespace App\Http\Controllers;

use App\Attribute;
use App\Bibliography;
use App\SearchableEntity;
use App\EntityType;
use App\File;
use App\Geodata;
use App\ThConcept;
use App\ThLanguage;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class SearchController extends Controller {
    private static $shebangPrefix = [
        'bibliography' => '!b ',
        'entities' => '!e ',
        'files' => '!f ',
        'geodata' => '!g ',
    ];

    public function searchGlobal(Request $request) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to search global')
            ], 403);
        }
        $q = $request->query('q');
        if(Str::startsWith($q, self::$shebangPrefix['bibliography'])) {
            $matches = Bibliography::search(Str::after($q, self::$shebangPrefix['bibliography']))->get();
            $matches->map(function($m) {
                $m->group = 'bibliography';
                return $m;
            });
        } else if(Str::startsWith($q, self::$shebangPrefix['entities'])) {
            $matches = SearchableEntity::search(Str::after($q, self::$shebangPrefix['entities']))->get();
            $matches->map(function($m) {
                $m->group = 'entities';
                return $m;
            });
        } else if(Str::startsWith($q, self::$shebangPrefix['files'])) {
            $files = File::search(Str::after($q, self::$shebangPrefix['files']));
            $matches = $files->get();
            $matches->map(function($m) {
                $m->group = 'files';
                $m->setFileInfo();
                return $m;
            });
        } else if(Str::startsWith($q, self::$shebangPrefix['geodata'])) {
            $matches = Geodata::search(Str::after($q, self::$shebangPrefix['geodata']))->get();
            $matches->map(function($m) {
                $m->group = 'geodata';
                return $m;
            });
        } else {
            $files = File::search($q)->get();
            $files->map(function($f) {
                $f->group = 'files';
                $f->setFileInfo();
                return $f;
            });
            $entities = SearchableEntity::search($q)->get();
            $entities->map(function($e) {
                $e->group = 'entities';
                return $e;
            });
            $geodata = Geodata::search($q)->get();
            $geodata->map(function($g) {
                $g->group = 'geodata';
                return $g;
            });
            $bibliography = Bibliography::search($q)->get();
            $bibliography->map(function($b) {
                $b->group = 'bibliography';
                return $b;
            });
            $matches = $files->concat($entities)
                ->concat($geodata)
                ->concat($bibliography)
                ->sortByDesc('relevance')
                ->values()
                ->all();
        }
        return response()->json($matches);
    }

    public function searchEntityByName(Request $request) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
            return response()->json([
                'error' => __('You do not have the permission to search for entities')
            ], 403);
        }
        $q = $request->query('q');
        $matches = SearchableEntity::where('name', 'ilike', '%'.$q.'%')
            ->orderBy('name')
            ->get();
        $matches->each->append(['ancestors']);
        return response()->json($matches);
    }

    public function searchEntityTypes(Request $request) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
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
        if(!$user->can('view_concepts_th')) {
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

    public function getConceptChildren($id, Request $request) {
        $user = auth()->user();
        if(!$user->can('view_concepts')) {
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
