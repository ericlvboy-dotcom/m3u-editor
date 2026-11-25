<?php

use App\Filament\Resources\MergedPlaylists\Pages\EditMergedPlaylist;
use App\Filament\Resources\MergedPlaylists\Pages\ViewMergedPlaylist;
use App\Models\MergedPlaylist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the merged playlist view page with the details tabs', function () {
    $merged = MergedPlaylist::factory()->for($this->user)->create();

    Livewire::test(ViewMergedPlaylist::class, ['record' => $merged->getRouteKey()])
        ->assertSuccessful()
        ->assertSeeText('Details')
        ->assertSeeText('Links')
        ->assertSeeText('Xtream API');
});

it('renders the merged playlist edit page without the moved detail tabs', function () {
    $merged = MergedPlaylist::factory()->for($this->user)->create();

    Livewire::test(EditMergedPlaylist::class, ['record' => $merged->id])
        ->assertSuccessful()
        ->assertSeeText('General')
        ->assertSeeText('Auth')
        ->assertSeeText('Output')
        ->assertDontSeeText('Manage playlist links and URL options.')
        ->assertDontSeeText('Xtream API connection details.');
});

it('loads and saves the merged playlist alphabetical sorting switches', function () {
    $merged = MergedPlaylist::factory()->for($this->user)->create([
        'user_agent' => 'M3U Editor Test',
        'live_group_sort' => 'az',
        'live_channel_sort' => 'default',
    ]);

    Livewire::test(EditMergedPlaylist::class, ['record' => $merged->getRouteKey()])
        ->assertFormSet(['sort_live_groups_alpha' => true, 'sort_live_channels_alpha' => false])
        ->fillForm(['sort_live_groups_alpha' => false, 'sort_live_channels_alpha' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    $merged->refresh();
    expect($merged->live_group_sort)->toBe('default')
        ->and($merged->live_channel_sort)->toBe('az');
});
