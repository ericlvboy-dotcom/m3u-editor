<?php

namespace App\Support;

final class PlaylistLabelFormatter
{
    /**
     * Normalize a group or channel label without changing its original value
     * when no transformation is requested.
     *
     * @param  'upper'|'lower'|'title'|null  $case
     */
    public static function format(string $label, ?string $case = null): string
    {
        return match ($case) {
            'upper' => mb_strtoupper($label, 'UTF-8'),
            'lower' => mb_strtolower($label, 'UTF-8'),
            'title' => mb_convert_case($label, MB_CASE_TITLE, 'UTF-8'),
            default => $label,
        };
    }

    /**
     * Replace a known old prefix instead of accumulating prefixes on repeated edits.
     * Passing an empty new prefix removes the old prefix.
     */
    public static function replacePrefix(string $label, string $oldPrefix, string $newPrefix): string
    {
        if ($oldPrefix !== '' && str_starts_with($label, $oldPrefix)) {
            $label = substr($label, strlen($oldPrefix));
        }

        if ($newPrefix === '') {
            return $label;
        }

        return str_starts_with($label, $newPrefix) ? $label : $newPrefix.$label;
    }
}
