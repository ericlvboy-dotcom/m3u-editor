<?php

use App\Support\PlaylistGroupSorter;

it('sorts group names alphabetically without changing the original array', function () {
    $groups = [['name' => 'Zulu'], ['name' => 'alpha'], ['name' => 'Bravo']];
    $name = fn (array $group): string => $group['name'];

    expect(array_column(PlaylistGroupSorter::sort($groups, 'az', $name), 'name'))
        ->toBe(['alpha', 'Bravo', 'Zulu']);
    expect(array_column(PlaylistGroupSorter::sort($groups, 'za', $name), 'name'))
        ->toBe(['Zulu', 'Bravo', 'alpha']);
    expect(array_column($groups, 'name'))->toBe(['Zulu', 'alpha', 'Bravo']);
});

it('preserves provider order and stable order for equal names', function () {
    $groups = [
        ['id' => 1, 'name' => 'Sports'],
        ['id' => 2, 'name' => 'sports'],
        ['id' => 3, 'name' => 'News'],
    ];
    $name = fn (array $group): string => $group['name'];

    expect(array_column(PlaylistGroupSorter::sort($groups, 'provider', $name), 'id'))
        ->toBe([1, 2, 3]);
    expect(array_column(PlaylistGroupSorter::sort($groups, 'az', $name), 'id'))
        ->toBe([3, 1, 2]);
    expect(array_column(PlaylistGroupSorter::sort($groups, 'za', $name), 'id'))
        ->toBe([1, 2, 3]);
});

it('rejects unsupported sorting modes instead of silently changing order', function () {
    PlaylistGroupSorter::sort([], 'manual', fn ($group): string => $group['name']);
})->throws(InvalidArgumentException::class);
