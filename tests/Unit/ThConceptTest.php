<?php

use App\ThConcept;
use App\ThConceptLabel;
use Illuminate\Support\Facades\App;


test('locale label', function () {
    $concept = ThConcept::find(1);
    $labelEn = $concept->getActiveLocaleLabel();
    expect($labelEn)->toEqual('Site');

    App::setLocale('de');
    $labelDe = $concept->getActiveLocaleLabel();
    expect($labelDe)->toEqual('Fundstelle');

    App::setLocale('en');
    expect($labelEn)->toEqual('Site');
});

test('relations', function () {
    $concept = ThConcept::with(['labels', 'narrowers', 'broaders'])->find(20);

    expect($concept->labels->count())->toEqual(1);
    expect($concept->labels[0]->id)->toEqual(26);
    expect($concept->labels[0]->language_id)->toEqual(1);
    expect($concept->labels[0]->concept_id)->toEqual(20);
    expect($concept->narrowers->count())->toEqual(0);
    expect($concept->broaders->count())->toEqual(1);
    expect($concept->broaders[0]->id)->toEqual(17);
});

test('get th concept lasteditor', function () {
    $concept = ThConcept::first();
    expect($concept->user->id)->toEqual(1);
    $label = ThConceptLabel::first();
    expect($label->user->id)->toEqual(1);
});
