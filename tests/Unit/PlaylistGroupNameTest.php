<?php

use App\Support\PlaylistGroupName as Name;

it('supports all seven group name formats', function () {
    expect(Name::format('sports news', '', Name::ORIGINAL))->toBe('sports news')
        ->and(Name::format('sports news', '', Name::TITLE))->toBe('Sports News')
        ->and(Name::format('sports news', '', Name::UPPER))->toBe('SPORTS NEWS')
        ->and(Name::format('sports news', '', Name::TITLE_DASH))->toBe('Sports - News')
        ->and(Name::format('sports news', '', Name::UPPER_DASH))->toBe('SPORTS - NEWS')
        ->and(Name::format('sports news', '', Name::TITLE_PIPE))->toBe('Sports | News')
        ->and(Name::format('sports news', '', Name::UPPER_PIPE))->toBe('SPORTS | NEWS');
});

it('preserves the exact literal prefix in every style', function () {
    foreach ([Name::ORIGINAL, Name::TITLE, Name::UPPER, Name::TITLE_DASH, Name::UPPER_DASH, Name::TITLE_PIPE, Name::UPPER_PIPE] as $style) {
        expect(Name::format('sports news', 'eN -- ', $style))->toStartWith('eN -- ');
    }

    expect(Name::format('sports news', 'EN ', Name::UPPER_PIPE))->toBe('EN SPORTS | NEWS')
        ->and(Name::format('sports', 'EN', Name::ORIGINAL))->toBe('ENsports');
});

it('rejects unknown styles rather than silently changing a name', function () {
    Name::format('Sports', 'EN ', 'unsupported');
})->throws(InvalidArgumentException::class);
