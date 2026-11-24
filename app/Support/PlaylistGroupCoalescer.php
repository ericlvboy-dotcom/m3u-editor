<?php

namespace App\Support;

/**
 * Coalesces consecutive source-group data into merged output groups.
 *
 * The caller supplies final displayed names (including literal prefixes).
 * Only exact names are coalesced. Channels retain their original order and
 * duplicates are deliberately retained. No database records are mutated.
 */
final class PlaylistGroupCoalescer
{
    /**
     * @template T
     * @param iterable<array{name: string, channels: iterable<T>}> $groups
     * @return array<int, array{name: string, channels: array<int, T>}>
     */
    public static function coalesce(iterable $groups): array
    {
        $result = [];
        $positions = [];

        foreach ($groups as $group) {
            $name = $group['name'];
            if (! array_key_exists($name, $positions)) {
                $positions[$name] = count($result);
                $result[] = ['name' => $name, 'channels' => []];
            }

            $index = $positions[$name];
            foreach ($group['channels'] as $channel) {
                $result[$index]['channels'][] = $channel;
            }
        }

        return $result;
    }
}
