<?php

use App\ThLanguage;


test('relations', function () {
    $l = ThLanguage::with(['labels'])->find(2);

    expect($l->labels->count())->toEqual(5);
    $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
        [
            'concept_id' => 1,
            'label' => 'Site',
        ],
        [
            'concept_id' => 2,
            'label' => 'Feature',
        ],
        [
            'concept_id' => 3,
            'label' => 'Find',
        ],
        [
            'concept_id' => 9,
            'label' => 'Pottery',
        ],
        [
            'concept_id' => 10,
            'label' => 'Stone',
        ],
    ], $l->labels->toArray(),
    ['concept_id', 'label']);
});

test('get language lasteditor', function () {
    $lang = ThLanguage::first();
    expect($lang->user->id)->toEqual(1);
});
