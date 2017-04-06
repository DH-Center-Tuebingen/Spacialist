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
        'series'
    ];
}
