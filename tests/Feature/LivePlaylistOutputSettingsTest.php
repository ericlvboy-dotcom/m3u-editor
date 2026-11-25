<?php

use App\Filament\Resources\Channels\Pages\ListChannels;
use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Models\Channel;
use App\Models\Group;
use App\Models\MergedPlaylist;
use App\Models\Playlist;
use App\Models\User;

it('regenerates live M3U output with the current prefix and case settings', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->for($user)->create([
        'live_group_prefix' => 'EN |',
        'live_group_case' => 'lowercase',
        'live_channel_case' => 'uppercase',
    ]);
    $group = Group::factory()->for($user)->for($playlist)->create(['name' => 'News', 'type' => 'live']);
    Channel::factory()->for($user)->for($playlist)->for($group)->create([
        'enabled' => true,
        'is_vod' => false,
        'name' => 'My Channel',
        'title' => 'My Channel',
    ]);

    $url = route('playlist.generate', ['uuid' => $playlist->uuid]);
    expect($this->get($url)->assertOk()->streamedContent())
        ->toContain('group-title="EN | news"', 'tvg-name="MY CHANNEL"');

    $playlist->update(['live_group_prefix' => 'EN']);
    expect($this->get($url)->assertOk()->streamedContent())->toContain('group-title="EN news"');

    $playlist->update(['live_group_prefix' => null]);
    expect($this->get($url)->assertOk()->streamedContent())->toContain('group-title="news"');

    $this->assertDatabaseHas('groups', ['id' => $group->id, 'name' => 'News']);
});

it('lists one live Xtream category and retains each channel after final names coincide', function (bool $merge) {
    $user = User::factory()->create();
    $source = Playlist::factory()->for($user)->create(['live_group_case' => 'uppercase']);
    $secondSource = $merge ? Playlist::factory()->for($user)->create() : $source;
    $output = $source;
    if ($merge) {
        $output = MergedPlaylist::factory()->for($user)->create(['live_group_case' => 'uppercase']);
        $output->playlists()->attach([$source->id, $secondSource->id], ['include_live' => true]);
    }

    $channelIds = [];
    foreach ([[$source, 'Sports'], [$secondSource, 'sports']] as [$playlist, $name]) {
        $group = Group::factory()->for($user)->for($playlist)->create(['name' => $name, 'type' => 'live']);
        $channelIds[] = Channel::factory()->for($user)->for($playlist)->for($group)->create([
            'enabled' => true,
            'is_vod' => false,
        ])->id;
    }

    $credentials = ['username' => $user->name, 'password' => $output->uuid];
    $categories = $this->getJson('/player_api.php?'.http_build_query($credentials + [
        'action' => 'get_live_categories',
    ]))->assertOk()->json();
    expect(array_column($categories, 'category_name'))->toBe(['SPORTS']);

    $streams = $this->getJson('/player_api.php?'.http_build_query($credentials + [
        'action' => 'get_live_streams',
        'category_id' => $categories[0]['category_id'],
    ]))->assertOk()->json();

    expect(array_column($streams, 'stream_id'))->toEqualCanonicalizing($channelIds)
        ->and(array_unique(array_column($streams, 'category_id')))->toBe([$categories[0]['category_id']]);
})->with([false, true]);

it('shows a merged playlist in both live management tables', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $source = Playlist::factory()->for($user)->create();
    $merged = MergedPlaylist::factory()->for($user)->create();
    $merged->playlists()->attach($source->id, ['include_live' => true]);

    expect((new ListChannels)->getTabs())->toHaveKey('merged_'.$merged->id)
        ->and((new ListGroups)->getTabs())->toHaveKey('merged_'.$merged->id);
});
