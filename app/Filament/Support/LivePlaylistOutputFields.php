<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

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

    public static function sortToggles(): array
    {
        return [
            Toggle::make('sort_live_groups_alpha')
                ->label(__('Sort live groups A–Z'))
                ->helperText(__('When off, use the existing group order.'))
                ->inline(false)
                ->default(false),
            Toggle::make('sort_live_channels_alpha')
                ->label(__('Sort live channels A–Z within each group'))
                ->helperText(__('When off, use the existing channel order.'))
                ->inline(false)
                ->default(false),
        ];
    }

    public static function storeSortToggles(array $data): array
    {
        $data['live_group_sort'] = ($data['sort_live_groups_alpha'] ?? false) ? 'az' : 'default';
        $data['live_channel_sort'] = ($data['sort_live_channels_alpha'] ?? false) ? 'az' : 'default';
        unset($data['sort_live_groups_alpha'], $data['sort_live_channels_alpha']);

        return $data;
    }
}
