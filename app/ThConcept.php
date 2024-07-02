<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ThConcept extends Model {
    use LogsActivity;

    protected $table = 'th_concept';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'concept_url',
        'concept_scheme',
        'is_top_concept',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logOnly(['id'])
            ->logFillable()
            ->dontLogIfAttributesChangedOnly(['user_id'])
            ->logOnlyDirty();
    }

    public static function getLabel($str) {
        $label = $str;
        $locale = \App::getLocale();
        try {
            $concept = self::getByString($str);
            $label = ThConceptLabel::selectRaw('label, short_name = ? as is_locale', [$locale])
                ->join('th_language', 'language_id', '=', 'th_language.id')
                ->where('concept_id', $concept->id)
                ->orderBy("is_locale", 'desc')
                ->orderBy('th_concept_label.id', 'asc')
                ->firstOrFail()->label;
        } catch (\Exception $e) {
            info("Could not find translation for: $str\n" . $e->getMessage());
        }
        return $label;
    }

    public static function getByString($str) {
        if (!isset($str) || $str === '') return null;

        $concept = ThConcept::where('concept_url', $str)->first();
        if (!isset($concept)) {
            $concept = ThConcept::whereHas('labels', function (Builder $query) use ($str) {
                $query->where('label', $str);
            })->first();
        }
        return $concept;
    }

    public static function getMap($lang = 'en') {
        // Some languages use different lang codes (e.g. from weblate) in Spacialist and ThesauRex
        if ($lang == 'ja') {
            $lang = 'jp';
        }
        $concepts = DB::select(DB::raw("
            WITH summary AS
            (
                SELECT th_concept.id, concept_url, is_top_concept, label, language_id, th_language.short_name,
                ROW_NUMBER() OVER
                (
                    PARTITION BY th_concept.id
                    ORDER BY th_concept.id, short_name != '$lang', concept_label_type
                ) AS rk
                FROM th_concept
                JOIN th_concept_label ON th_concept_label.concept_id = th_concept.id
                JOIN th_language ON language_id = th_language.id
            )
            SELECT id, concept_url, is_top_concept, label, language_id, short_name
            FROM summary s
            WHERE s.rk = 1")->getValue(DB::connection()->getQueryGrammar()));

        $conceptMap = [];

        foreach ($concepts as $concept) {
            $url = $concept->concept_url;
            unset($concept->concept_url);
            $conceptMap[$url] = $concept;
        }

        return $conceptMap;
    }

    public static function getChildren($url, $recursive = true) {
        $id = self::where('concept_url', $url)->value('id');
        if (!isset($id)) return [];

        $query = "SELECT br.broader_id, br.narrower_id, c.*
        FROM th_broaders br
        JOIN th_concept as c on c.id = br.narrower_id
        WHERE broader_id = $id";

        if ($recursive) {
            $query = "
                WITH RECURSIVE
                top AS (
                    $query
                    UNION
                    SELECT br.broader_id, br.narrower_id, c2.*
                    FROM top t, th_broaders br
                    JOIN th_concept as c2 on c2.id = br.narrower_id
                    WHERE t.narrower_id = br.broader_id
                )
                SELECT *
                FROM top
            ";
        }
        return DB::select($query);
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function labels() {
        return $this->hasMany('App\ThConceptLabel', 'concept_id');
    }

    public function notes() {
        return $this->hasMany('App\ThConceptNote', 'concept_id');
    }

    public function narrowers() {
        return $this->belongsToMany('App\ThConcept', 'th_broaders', 'broader_id', 'narrower_id');
    }

    public function broaders() {
        return $this->belongsToMany('App\ThConcept', 'th_broaders', 'narrower_id', 'broader_id');
    }

    public function files() {
        return $this->belongsToMany('App\File', 'file_tags', 'concept_id', 'file_id');
    }
}
