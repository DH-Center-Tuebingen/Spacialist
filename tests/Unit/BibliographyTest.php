<?php

use Illuminate\Http\Request;

use App\Bibliography;


test('relations', function () {
    $b = Bibliography::with(['entities'])->find(1318);

    expect($b->entities[0]->id)->toEqual(1);
    expect($b->entities[0]->pivot->attribute_id)->toEqual(15);
    expect($b->entities[0]->pivot->description)->toEqual('See Page 10');
});

test('parsing request', function () {
    $r = new Request();
    $r->replace([
        'year' => 1999,
        'entry_type' => 'book',
        'author' => 'Book Author',
        'publisher' => 'Spacialist',
        'title' => 'Book Title',
    ]);

    $user = new \stdClass;
    $user->id = 1;

    $ranges = array_merge([''], range('a', 'z'), range('A', 'Z'));
    foreach($ranges as $letter) {
        $b = new Bibliography();
        $b->title = 'Title';
        $b->entry_type = 'article';
        $b->citekey = 'Book Author_Book_bt_1999'.$letter;
        $b->user_id = 1;
        $b->save();
    }

    $bib = new Bibliography();
    $bib->fieldsFromRequest($r, $user);

    $newBib = Bibliography::orderBy('id', 'desc')->first();
    expect($newBib->id)->toEqual($bib->id);
    expect($newBib->title)->toEqual('Book Title');
    expect($newBib->citekey)->toEqual('Book Author_Book_bt_1999aa');
});

test('get bibliography entry lasteditor', function () {
    $entry = Bibliography::first();
    expect($entry->user->id)->toEqual(1);
});
