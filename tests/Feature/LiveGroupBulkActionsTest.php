<?php

use App\Filament\Resources\Groups\Pages\ListGroups;
use App\Models\Group;
use App\Models\Playlist;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->playlist = Playlist::factory()->for($this->user)->create(['auto_sort_groups' => false]);
});

it('renumbers only selected source live groups alphabetically', function () {
    $z = Group::factory()->for($this->playlist)->for($this->user)->create(['name' => 'Zebra', 'type' => 'live', 'sort_order' => 1]);
    $a = Group::factory()->for($this->playlist)->for($this->user)->create(['name' => 'Alpha', 'type' => 'live', 'sort_order' => 2]);
    $vod = Group::factory()->for($this->playlist)->for($this->user)->create(['name' => 'Movie', 'type' => 'vod', 'sort_order' => 8]);
    $other = Playlist::factory()->for($this->user)->create(['auto_sort_groups' => false]);
    $untouched = Group::factory()->for($other)->for($this->user)->create(['name' => 'Other', 'type' => 'live', 'sort_order' => 9]);

    Livewire::test(ListGroups::class)
        ->callAction('bulk-renumber-live-groups', ['playlist_id' => $this->playlist->id, 'start' => 100, 'step' => 5])
        ->assertHasNoErrors();

    expect($a->fresh()->sort_order)->toBe(100)
        ->and($z->fresh()->sort_order)->toBe(105)
        ->and($vod->fresh()->sort_order)->toBe(8)
        ->and($untouched->fresh()->sort_order)->toBe(9);
});

it('does not renumber when provider auto sort is enabled', function () {
    $group = Group::factory()->for($this->playlist)->for($this->user)->create(['name' => 'News', 'type' => 'live', 'sort_order' => 7]);
    $this->playlist->update(['auto_sort_groups' => true]);

    Livewire::test(ListGroups::class)
        ->callAction('bulk-renumber-live-groups', ['playlist_id' => $this->playlist->id, 'start' => 100, 'step' => 1])
        ->assertHasNoErrors();

    expect($group->fresh()->sort_order)->toBe(7);
});

it('prefixes only live groups without duplicating an existing matching prefix', function () {
    $live = Group::factory()->for($this->playlist)->for($this->user)->create(['name' => 'Action', 'type' => 'live']);
    $already = Group::factory()->for($this->playlist)->for($this->user)->create(['name' => '247 - Sports', 'type' => 'live']);
    $vod = Group::factory()->for($this->playlist)->for($this->user)->create(['name' => 'Movies', 'type' => 'vod']);

    Livewire::test(ListGroups::class)
        ->callAction('prefix-live-groups', ['playlist_id' => $this->playlist->id, 'prefix' => '247'])
        ->assertHasNoErrors();

    expect($live->fresh()->name)->toBe('247 - Action')
        ->and($already->fresh()->name)->toBe('247 - Sports')
        ->and($vod->fresh()->name)->toBe('Movies');
});
