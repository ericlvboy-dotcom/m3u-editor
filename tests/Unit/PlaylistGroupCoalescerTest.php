<?php

use App\Support\PlaylistGroupCoalescer;

it('merges only identical final group names and keeps all channels', function () {
    $groups = [
        ['name' => 'EN - Sports', 'channels' => ['one', 'two']],
        ['name' => 'FR - Sports', 'channels' => ['trois']],
        ['name' => 'EN - Sports', 'channels' => ['two', 'four']],
        ['name' => 'EN - sports', 'channels' => ['five']],
    ];

    expect(PlaylistGroupCoalescer::coalesce($groups))->toBe([
        ['name' => 'EN - Sports', 'channels' => ['one', 'two', 'two', 'four']],
        ['name' => 'FR - Sports', 'channels' => ['trois']],
        ['name' => 'EN - sports', 'channels' => ['five']],
    ]);
});

it('keeps the first group position and preserves source channel order', function () {
    $groups = [
        ['name' => 'B', 'channels' => [10]],
        ['name' => 'A', 'channels' => [20]],
        ['name' => 'B', 'channels' => [30, 40]],
    ];

    expect(PlaylistGroupCoalescer::coalesce($groups))->toBe([
        ['name' => 'B', 'channels' => [10, 30, 40]],
        ['name' => 'A', 'channels' => [20]],
    ]);
});
