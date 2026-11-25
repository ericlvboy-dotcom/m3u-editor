<?php

namespace App\Filament\Support;

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
}
