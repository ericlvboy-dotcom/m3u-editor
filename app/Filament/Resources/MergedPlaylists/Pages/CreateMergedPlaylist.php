<?php

namespace App\Filament\Resources\MergedPlaylists\Pages;

use App\Filament\Resources\MergedPlaylists\MergedPlaylistResource;
use App\Filament\Support\LivePlaylistOutputFields;
use Filament\Resources\Pages\CreateRecord;

class CreateMergedPlaylist extends CreateRecord
{
    protected static string $resource = MergedPlaylistResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return LivePlaylistOutputFields::storeSortToggles($data);
    }
}
