<?php

use App\Support\LiveGroupName;

it('combines duplicate names across sources without dropping channels', function () {
    $groups = LiveGroupName::coalesce([
        ['group' => 'USA Sports', 'channel' => 'source-a-channel-1'],
        ['group' => 'Canada News', 'channel' => 'source-b-channel-1'],
        ['group' => 'USA Sports', 'channel' => 'source-b-channel-2'],
        ['group' => 'USA Sports', 'channel' => 'source-a-channel-3'],
    ]);

    expect(array_keys($groups))->toBe(['USA Sports', 'Canada News'])
        ->and($groups['USA Sports'])->toBe([
            'source-a-channel-1',
            'source-b-channel-2',
            'source-a-channel-3',
        ]);
});

it('coalesces after applying requested name formatting', function () {
    $groups = LiveGroupName::coalesce([
        ['group' => 'USA Sports', 'channel' => 1],
        ['group' => 'usa sports', 'channel' => 2],
    ], 'lowercase');

    expect($groups)->toBe(['usa sports' => [1, 2]]);
});
