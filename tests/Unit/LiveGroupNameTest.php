<?php

use App\Support\LiveGroupName;

it('formats live group titles without altering the original by default', function () {
    expect(LiveGroupName::format('uSa SPORTS'))->toBe('uSa SPORTS')
        ->and(LiveGroupName::format('uSa SPORTS', 'uppercase'))->toBe('USA SPORTS')
        ->and(LiveGroupName::format('uSa SPORTS', 'lowercase'))->toBe('usa sports')
        ->and(LiveGroupName::format('uSa SPORTS', 'title'))->toBe('Usa Sports');
});

it('produces the same key for matching final group names from different sources', function () {
    expect(LiveGroupName::key('USA Sports', 'lowercase'))
        ->toBe(LiveGroupName::key('usa sports', 'lowercase'));
});

it('does not silently combine distinct original names', function () {
    expect(LiveGroupName::key('USA Sports'))->not->toBe(LiveGroupName::key('usa sports'));
});

it('rejects unknown formatting options', function () {
    LiveGroupName::format('Sports', 'unknown');
})->throws(InvalidArgumentException::class);
