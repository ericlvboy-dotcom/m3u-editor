<?php

namespace App\Support;

/**
 * Shared normalization for exported live group names.
 *
 * This is deliberately independent of playlist source IDs: two groups with the
 * same final displayed name must have the same grouping key.
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

    public static function key(string $name, string $mode = 'original'): string
    {
        return self::format($name, $mode);
    }
}
