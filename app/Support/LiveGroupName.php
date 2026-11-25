<?php

namespace App\Support;

/**
 * Shared normalization and coalescing of final live-group names.
 *
 * The exported category identity is its final displayed name, not its source
 * group ID. This helper does not deduplicate channels.
 */
final class LiveGroupName
{
    public static function format(string $name, string $mode = 'original'): string
    {
        return match ($mode) {
            'original' => $name,
            'uppercase' => mb_strtoupper($name, 'UTF-8'),
            'lowercase' => mb_strtolower($name, 'UTF-8'),
            'title' => mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'),
            default => throw new \InvalidArgumentException("Unsupported name format: {$mode}"),
        };
    }

    public static function display(string $name, string $mode, ?string $prefix): string
    {
        $name = self::format($name, $mode);

        return $prefix === null || trim($prefix) === '' ? $name : trim($prefix).' '.$name;
    }

    public static function key(string $name, string $mode = 'original'): string
    {
        return self::format($name, $mode);
    }

    /**
     * Group channel records by their final group name while retaining every
     * record and the first-seen group order.
     *
     * @param  iterable<array{group:string,channel:mixed}>  $entries
     * @return array<string, list<mixed>>
     */
    public static function coalesce(iterable $entries, string $mode = 'original'): array
    {
        $groups = [];

        foreach ($entries as $entry) {
            $name = self::key($entry['group'], $mode);
            $groups[$name][] = $entry['channel'];
        }

        return $groups;
    }
}
