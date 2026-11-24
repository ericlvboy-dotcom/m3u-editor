<?php

use App\Support\MergedPlaylistGroupProjection;

it('formats the provider portion, merges identical final names and sorts merged output', function () {
    $sources = [
        ['name' => 'sports', 'prefix' => 'EN | ', 'channels' => [1, 2]],
        ['name' => 'news', 'prefix' => 'EN | ', 'channels' => [3]],
        ['name' => 'SPORTS', 'prefix' => 'EN | ', 'channels' => [4, 2]],
        ['name' => 'sports', 'prefix' => 'FR | ', 'channels' => [5]],
    ];

    expect(MergedPlaylistGroupProjection::build($sources, 'upper', 'az'))->toBe([
        ['name' => 'EN | NEWS', 'channels' => [3]],
        ['name' => 'EN | SPORTS', 'channels' => [1, 2, 4, 2]],
        ['name' => 'FR | SPORTS', 'channels' => [5]],
    ]);
});

it('keeps provider order and does not accumulate prefixes on repeated projection', function () {
    $sources = [
        ['name' => 'zulu', 'prefix' => 'EN-', 'channels' => ['a']],
        ['name' => 'alpha', 'prefix' => 'EN-', 'channels' => ['b']],
    ];

    $first = MergedPlaylistGroupProjection::build($sources);
    expect(MergedPlaylistGroupProjection::build($sources))->toBe($first);
    expect(array_column($first, 'name'))->toBe(['EN-zulu', 'EN-alpha']);
});
