<?php

use App\Support\PlaylistLabelFormatter;

it('preserves labels when no capitalization is selected', function () {
    expect(PlaylistLabelFormatter::format('BBC One HD'))->toBe('BBC One HD');
});

it('formats both group and channel labels with UTF-8 support', function () {
    expect(PlaylistLabelFormatter::format('éTÉ SPORTS', 'lower'))->toBe('été sports')
        ->and(PlaylistLabelFormatter::format('été sports', 'upper'))->toBe('ÉTÉ SPORTS')
        ->and(PlaylistLabelFormatter::format('éTÉ SPORTS', 'title'))->toBe('Été Sports');
});

it('replaces and removes a group prefix without stacking duplicates', function () {
    expect(PlaylistLabelFormatter::replacePrefix('EN | Sports', 'EN | ', 'FR | '))->toBe('FR | Sports')
        ->and(PlaylistLabelFormatter::replacePrefix('EN | Sports', 'EN | ', ''))->toBe('Sports')
        ->and(PlaylistLabelFormatter::replacePrefix('FR | Sports', '', 'FR | '))->toBe('FR | Sports');
});

it('does not remove unrelated prefixes or duplicate a newly applied prefix', function () {
    expect(PlaylistLabelFormatter::replacePrefix('UK | Sports', 'EN | ', 'FR | '))->toBe('FR | UK | Sports')
        ->and(PlaylistLabelFormatter::replacePrefix('FR | Sports', 'EN | ', 'FR | '))->toBe('FR | Sports')
        ->and(PlaylistLabelFormatter::replacePrefix('EN | Sports', 'EN | ', ''))->toBe('Sports');
});
