<?php

use App\Filament\Resources\Channels\Pages\ListChannels;
use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Models\Channel;
use App\Models\Group;
use App\Models\MergedPlaylist;
use App\Models\Playlist;
use App\Models\User;
use Livewire\Livewire;

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

    $m3u = $this->get(route('playlist.generate', ['uuid' => $output->uuid]))->assertOk()->streamedContent();
    expect(substr_count($m3u, 'group-title="SPORTS"'))->toBe(2);

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

it('shows source playlists without merged playlist tabs in live management', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $source = Playlist::factory()->for($user)->create();
    $merged = MergedPlaylist::factory()->for($user)->create();
    $merged->playlists()->attach($source->id, ['include_live' => true]);

    $groupTabs = Livewire::test(ListGroups::class)->instance()->getTabs();
    $channelTabs = Livewire::test(ListChannels::class)->instance()->getTabs();

    expect($groupTabs)->toHaveKey($source->id)
        ->not->toHaveKey('merged_'.$merged->id)
        ->and($channelTabs)->toHaveKey('playlist_'.$source->id)
        ->not->toHaveKey('merged_'.$merged->id);
});

it('sorts merged live groups and their channels independently in the exported playlist', function () {
    $user = User::factory()->create();
    $source = Playlist::factory()->for($user)->create();
    $merged = MergedPlaylist::factory()->for($user)->create();
    $merged->playlists()->attach($source->id, ['include_live' => true]);

    foreach (['Zulu', 'Alpha'] as $groupOrder => $groupName) {
        $group = Group::factory()->for($user)->for($source)->create([
            'name' => $groupName,
            'sort_order' => $groupOrder + 1,
            'type' => 'live',
        ]);

        foreach (['Two', 'One'] as $channelOrder => $title) {
            Channel::factory()->for($user)->for($source)->for($group)->create([
                'title' => "$groupName $title",
                'name' => "$groupName $title",
                'name_custom' => null,
                'title_custom' => null,
                'sort' => $channelOrder + 1,
                'enabled' => true,
                'is_vod' => false,
            ]);
        }
    }

    $titles = function () use ($merged): array {
        $content = $this->get(route('playlist.generate', ['uuid' => $merged->uuid]))->assertOk()->streamedContent();
        preg_match_all('/tvg-name="([^"]+)"/', $content, $matches);

        return $matches[1];
    };

    expect($titles())->toBe(['Zulu Two', 'Zulu One', 'Alpha Two', 'Alpha One']);

    $merged->update(['live_group_sort' => 'az']);
    expect($titles())->toBe(['Alpha Two', 'Alpha One', 'Zulu Two', 'Zulu One']);

    $merged->update(['live_channel_sort' => 'az']);
    expect($titles())->toBe(['Alpha One', 'Alpha Two', 'Zulu One', 'Zulu Two']);

    $merged->update(['live_group_sort' => 'default']);
    expect($titles())->toBe(['Zulu One', 'Zulu Two', 'Alpha One', 'Alpha Two']);
});
