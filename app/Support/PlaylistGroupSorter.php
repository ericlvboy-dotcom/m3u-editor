<?php

namespace App\Support;

/**
 * Stable group ordering for source and merged playlist exports.
 *
 * This helper does not mutate the supplied groups or persist an ordering
 * decision. Callers must invoke it only for an explicitly selected sort mode.
 */
final class PlaylistGroupSorter
{
    public static function sort(array $groups, string $mode, callable $name): array
    {
        if ($mode === 'provider') {
            return array_values($groups);
        }

        if (! in_array($mode, ['az', 'za'], true)) {
            throw new \InvalidArgumentException("Unknown group sort mode: {$mode}");
        }

        $indexed = [];
        foreach (array_values($groups) as $index => $group) {
            $indexed[] = ['index' => $index, 'group' => $group, 'name' => $name($group)];
        }

        usort($indexed, static function (array $left, array $right) use ($mode): int {
            $comparison = strnatcasecmp($left['name'], $right['name']);
            if ($mode === 'za') {
                $comparison = -$comparison;
            }

            return $comparison !== 0 ? $comparison : $left['index'] <=> $right['index'];
        });

        return array_column($indexed, 'group');
    }
}
