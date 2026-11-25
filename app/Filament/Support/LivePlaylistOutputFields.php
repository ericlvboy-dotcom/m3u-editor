<?php

namespace App\Filament\Support;

use App\Models\MergedPlaylist;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

final class LivePlaylistOutputFields
{
    public static function caseFields(): array
    {
        $options = [
            'original' => __('Original'),
            'uppercase' => __('Uppercase'),
            'title' => __('Title Case'),
            'lowercase' => __('Lowercase'),
        ];

        return [
            Select::make('live_group_case')->label(__('Live group name case'))->options($options)->default('original')->required(),
            Select::make('live_channel_case')->label(__('Live channel name case'))->options($options)->default('original')->required(),
        ];
    }

    public static function mergedSortAction(Closure $activeTab): Action
    {
        $playlistQuery = fn () => MergedPlaylist::query()
            ->where('user_id', auth()->id())
            ->whereKey((int) substr((string) $activeTab(), 7));
        $options = ['default' => __('Default'), 'az' => __('A–Z')];

        return Action::make('sortMergedPlaylist')
            ->label(__('Sort merged playlist'))
            ->visible(fn (): bool => str_starts_with((string) $activeTab(), 'merged_'))
            ->fillForm(fn (): array => $playlistQuery()->firstOrFail()
                ->only(['live_group_sort', 'live_channel_sort']))
            ->schema([
                Select::make('live_group_sort')->label(__('Live groups'))->options($options)->required(),
                Select::make('live_channel_sort')->label(__('Live channels within each group'))->options($options)->required(),
            ])
            ->action(function (array $data) use ($playlistQuery): void {
                $playlistQuery()->firstOrFail()->update($data);
            })
            ->successNotificationTitle(__('Merged playlist sort saved'));
    }
}
