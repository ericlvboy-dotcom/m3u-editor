<?php

namespace App\Support;

/**
 * Produces deterministic, non-mutating group output for a merged playlist.
 *
 * Each source group must supply its provider name, literal prefix and channels.
 * This is a projection of source data, not a second import or database merge.
 */
final class MergedPlaylistGroupProjection
{
    /**
     * @template T
     *
     * @param iterable<array{name: string, prefix?: string, channels: iterable<T>}> $sourceGroups
     *
     * @return array<int, array{name: string, channels: array<int, T>}>
     */
    public static function build(iterable $sourceGroups, string $format = 'original', string $order = 'provider'): array
    {
        $displayGroups = [];

        foreach ($sourceGroups as $group) {
            $displayGroups[] = [
                'name' => PlaylistGroupName::format($group['name'], $group['prefix'] ?? '', $format),
                'channels' => $group['channels'],
            ];
        }

        $coalesced = PlaylistGroupCoalescer::coalesce($displayGroups);

        return PlaylistGroupSorter::sort($coalesced, $order, static fn (array $group): string => $group['name']);
    }
}
