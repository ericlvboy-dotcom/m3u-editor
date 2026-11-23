<?php

namespace App\Filament\Resources\Groups\Pages;

use App\Filament\Resources\Groups\GroupResource;
use App\Jobs\GroupFindAndReplace;
use App\Jobs\GroupFindAndReplaceReset;
use App\Models\Playlist;
use App\Models\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\DB;
use App\Services\FindReplaceService;
use App\Services\MergedGroupService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return __('Manage live groups.');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, string $model): Model {
                    $data['user_id'] = auth()->id();
                    $data['custom'] = true;
                    $data['type'] = 'live';

                    return $model::create($data);
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Group created'))
                        ->body(__('You can now assign channels to this group from the Channels section.')),
                )
                ->successRedirectUrl(fn (Model $record): string => GroupResource::getUrl('edit', ['record' => $record]))
                ->slideOver(),
            MergedGroupService::createMergedGroupAction('live'),
            Action::make('bulk-renumber-live-groups')
                ->label(__('Bulk Renumber'))
                ->icon('heroicon-o-numbered-list')
                ->schema([
                    Select::make('playlist_id')
                        ->label(__('Source playlist'))
                        ->options(fn () => Playlist::query()->where('user_id', auth()->id())->orderBy('name')->pluck('name', 'id'))
                        ->required()->searchable(),
                    TextInput::make('start')->label(__('Starting Sort Order #'))
                        ->numeric()->integer()->minValue(0)->required()->default(100),
                    TextInput::make('step')->label(__('Increment By'))
                        ->numeric()->integer()->minValue(1)->required()->default(1),
                ])
                ->modalDescription(__('Assign Sort Order numbers to this source\'s Live TV groups alphabetically by displayed name. This is a one-time action; it does not run during sync. Disable Group Auto Sort on the source first.'))
                ->requiresConfirmation()
                ->modalSubmitActionLabel(__('Renumber groups'))
                ->action(function (array $data): void {
                    $playlist = Playlist::query()->where('user_id', auth()->id())->findOrFail($data['playlist_id']);
                    if ($playlist->auto_sort_groups) {
                        Notification::make()->danger()->title(__('Disable Group Auto Sort for this source before renumbering.'))->send();
                        return;
                    }
                    $start = (int) $data['start'];
                    $step = (int) $data['step'];
                    if ($start < 0 || $step < 1) {
                        Notification::make()->danger()->title(__('Invalid numbering values.'))->send();
                        return;
                    }
                    $groups = Group::query()->where('user_id', auth()->id())
                        ->where('playlist_id', $playlist->id)->where('type', 'live')
                        ->whereNull('parent_id')->get()
                        ->sort(fn ($a, $b) => strnatcasecmp($a->name ?: $a->name_internal ?: '', $b->name ?: $b->name_internal ?: ''))
                        ->values();
                    if ($groups->isEmpty()) {
                        Notification::make()->warning()->title(__('No Live TV groups found for this source.'))->send();
                        return;
                    }
                    $last = $start + ($groups->count() - 1) * $step;
                    if ($last > 2147483647) {
                        Notification::make()->danger()->title(__('Sort Order range is too large.'))->send();
                        return;
                    }
                    // Avoid assigning numbers already used by another source.
                    $taken = Group::query()->where('user_id', auth()->id())->where('type', 'live')
                        ->where('playlist_id', '!=', $playlist->id)
                        ->whereBetween('sort_order', [$start, $last])
                        ->pluck('sort_order')->map(fn ($n) => (int) $n)->flip();
                    foreach ($groups as $index => $group) {
                        if ($taken->has($start + $index * $step)) {
                            Notification::make()->danger()->title(__('Number range overlaps another source. Choose a different starting number.'))->send();
                            return;
                        }
                    }
                    DB::transaction(function () use ($groups, $start, $step): void {
                        foreach ($groups as $index => $group) {
                            $group->update(['sort_order' => $start + $index * $step]);
                        }
                    });
                    Notification::make()->success()->title(__('Live TV groups renumbered A–Z'))
                        ->body(__(':count groups updated.', ['count' => $groups->count()]))->send();
                }),

            ActionGroup::make([
                Action::make('find-replace')
                    ->label(__('Find & Replace'))
                    ->schema(fn () => FindReplaceService::getHeaderActionSchema('groups'))
                    ->action(function (array $data): void {
                        app('Illuminate\Contracts\Bus\Dispatcher')
                            ->dispatch(new GroupFindAndReplace(
                                user_id: auth()->id(),
                                use_regex: $data['use_regex'] ?? true,
                                find_replace: $data['find_replace'] ?? '',
                                replace_with: $data['replace_with'] ?? '',
                                playlist_id: $data['playlist'] ?? null,
                                group_type: 'live',
                            ));
                    })->after(function () {
                        Notification::make()
                            ->success()
                            ->title(__('Find & Replace started'))
                            ->body(__('Find & Replace working in the background. You will be notified once the process is complete.'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('gray')
                    ->modalIcon('heroicon-o-magnifying-glass')
                    ->modalDescription(__('Select what you would like to find and replace in your live group names.'))
                    ->modalSubmitActionLabel(__('Replace now')),
                Action::make('find-replace-reset')
                    ->label(__('Undo Find & Replace'))
                    ->schema(fn () => FindReplaceService::getHeaderResetSchema())
                    ->action(function (array $data): void {
                        app('Illuminate\Contracts\Bus\Dispatcher')
                            ->dispatch(new GroupFindAndReplaceReset(
                                user_id: auth()->id(),
                                playlist_id: $data['playlist'] ?? null,
                                group_type: 'live',
                            ));
                    })->after(function () {
                        Notification::make()
                            ->success()
                            ->title(__('Find & Replace reset started'))
                            ->body(__('Find & Replace reset working in the background. You will be notified once the process is complete.'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->modalIcon('heroicon-o-arrow-uturn-left')
                    ->modalDescription(__('Reset group names back to their original imported values. This will undo any find & replace changes for the selected playlist.'))
                    ->modalSubmitActionLabel(__('Reset now')),
            ])->button()->label(__('Actions')),
        ];
    }

    /**
     * @deprecated Override the `table()` method to configure the table.
     */
    protected function getTableQuery(): ?Builder
    {
        return static::getResource()::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->where('type', 'live');
    }

    public function getTabs(): array
    {
        return self::setupTabs();
    }

    public static function setupTabs($relationId = null): array
    {
        $where = [
            ['user_id', auth()->id()],
        ];

        // Fetch all the playlists for the current user, these will be our grouping tabs
        $playlists = Playlist::where($where)
            ->orderBy('name')
            ->get();

        // Return tabs
        return $playlists->mapWithKeys(fn ($playlist) => [
            $playlist->id => Tab::make($playlist->name)
                ->modifyQueryUsing(fn ($query) => $query->where([
                    ['playlist_id', $playlist->id],
                    ['type', 'live'],
                ]))
                ->badge($playlist->liveGroups()->count()),
        ])->toArray();
    }
}
