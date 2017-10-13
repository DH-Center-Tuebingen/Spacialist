<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Literature extends Model
{

    protected $table = 'literature';
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
        'citekey'
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
}
