<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Nicolaslopezj\Searchable\SearchableTrait;

class Bibliography extends Model
{
    use SearchableTrait;

    protected $table = 'bibliography';
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
        'author',
        'editor',
        'title',
        'journal',
        'year',
        'pages',
        'volume',
        'number',
        'booktitle',
        'publisher',
        'address',
        'misc',
        'howpublished',
        'type',
        'annote',
        'chapter',
        'crossref',
        'edition',
        'institution',
        'key',
        'month',
        'note',
        'organization',
        'school',
        'series',
        'citekey',
        'user_id'
    ];

    protected $searchable = [
        'columns' => [
            'author' => 10,
            'editor' => 10,
            'title' => 10,
            'journal' => 10,
            'year' => 10,
            'pages' => 5,
            'volume' => 5,
            'number' => 5,
            'booktitle' => 10,
            'publisher' => 10,
            'address' => 5,
            'misc' => 8,
            'howpublished' => 5,
            'type' => 5,
            'annote' => 5,
            'chapter' => 5,
            'crossref' => 5,
            'edition' => 5,
            'institution' => 5,
            'key' => 5,
            'month' => 5,
            'note' => 5,
            'organization' => 5,
            'school' => 5,
            'series' => 5,
            'citekey' => 5
        ]
    ];

    const patchRules = [
        'author'    => 'string',
        'editor'    => 'string',
        'title'     => 'string',
        'journal'   => 'string',
        'year'      => 'string',
        'pages'     => 'string',
        'volume'    => 'string',
        'number'    => 'string',
        'booktitle' => 'string',
        'publisher' => 'string',
        'address'   => 'string',
        'misc'      => 'string',
        'howpublished'=> 'string',
        'type'      => 'string',
        'annote'    => 'string',
        'chapter'   => 'string',
        'crossref'  => 'string',
        'edition'   => 'string',
        'institution'=> 'string',
        'key'       => 'string',
        'month'     => 'string',
        'note'      => 'string',
        'organization'=> 'string',
        'school'    => 'string',
        'series'    => 'string'
    ];

    public function fieldsFromRequest($request, $user) {
        $fields = $request->toArray();
        if(!isset($fields['title'])) {
            $fields['title'] = 'No Title';
        }
        foreach($fields as $key => $value){
            $this->{$key} = $value;
        }

        $this->citekey = self::computeCitationKey($this->toArray());
        $this->user_id = $user->id;
        $this->save();
    }

    public function referenceCount() {
        return Reference::where('bibliography_id', $this->id)->count();
    }

    public static function computeCitationKey($fields) {
        $key;
        if(isset($fields['author'])) {
            $key = $fields['author'];
        } else {
            $key = $fields['title'];
        }
        // Use first two letters of author/title as key with only first letter uppercase
        $key = ucwords(Str::lower(substr($key, 0, 2))) . ':';
        if(isset($fields['year'])) {
            $key .= $fields['year'];
        } else {
            $key .= '0000';
        }

        $initalKey = $key;
        $suffixes = array_merge(range('a', 'z'), range('A', 'Z'));
        $suffixesCount = count($suffixes);
        $i = 0;
        $j = 0;
        while(self::where('citekey', $key)->first() !== null) {
            // if single letter was not enough to be unique, add another
            if($i == $suffixesCount) {
                if($j == $suffixesCount) $j = 0;
                $initalKey = $initalKey . $suffixes[$j++];
                $i = 0;
            }
            $key = $initalKey . $suffixes[$i++];
        }
        return $key;
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function entities() {
        return $this->belongsToMany('App\Entity', 'references')->withPivot('description', 'attribute_id');
    }
}
