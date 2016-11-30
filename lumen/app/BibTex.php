<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BibTex extends Model
{

    protected $table = 'bib_tex';
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
    ];
}
