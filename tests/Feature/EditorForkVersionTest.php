<?php

use App\Providers\VersionServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('checks the editor fork for updates and ignores the old upstream version cache', function () {
    Cache::put('app.remoteVersion', '99.0.0');
    Http::preventStrayRequests();
    Http::fake([
        'https://raw.githubusercontent.com/ericlvboy-dotcom/m3u-editor/refs/heads/rebuild/clean-upstream-20261124/config/dev.php' => Http::response("'version' => '0.14.7',"),
    ]);

    expect(VersionServiceProvider::getRemoteVersion())->toBe('0.14.7');
    Http::assertSentCount(1);
});

it('fetches editor release logs from the fork', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.github.com/repos/ericlvboy-dotcom/m3u-editor/releases?*' => Http::response([]),
    ]);

    VersionServiceProvider::fetchReleases(refresh: true);

    Http::assertSentCount(1);
});
