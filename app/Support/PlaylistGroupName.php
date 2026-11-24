<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Pure group-name formatting for source and merged playlist output.
 *
 * Prefixes are deliberately kept separate from provider names so repeated
 * exports cannot accumulate a prefix or alter its exact spelling.
 */
final class PlaylistGroupName
{
    public const ORIGINAL = 'original';
    public const TITLE = 'title';
    public const UPPER = 'upper';
    public const TITLE_DASH = 'title_dash';
    public const UPPER_DASH = 'upper_dash';
    public const TITLE_PIPE = 'title_pipe';
    public const UPPER_PIPE = 'upper_pipe';

    public static function format(string $providerName, string $prefix = '', string $style = self::ORIGINAL): string
    {
        $formatted = match ($style) {
            self::ORIGINAL => $providerName,
            self::TITLE => self::title($providerName),
            self::UPPER => mb_strtoupper($providerName),
            self::TITLE_DASH => self::joinWords($providerName, ' - ', false),
            self::UPPER_DASH => self::joinWords($providerName, ' - ', true),
            self::TITLE_PIPE => self::joinWords($providerName, ' | ', false),
            self::UPPER_PIPE => self::joinWords($providerName, ' | ', true),
            default => throw new InvalidArgumentException("Unknown group-name format: {$style}"),
        };

        return $prefix.$formatted;
    }

    private static function title(string $name): string
    {
        return mb_convert_case($name, MB_CASE_TITLE);
    }

    private static function joinWords(string $name, string $separator, bool $upper): string
    {
        // A provider's existing separators delimit words, but never touch
        // the literal prefix. Do not generate a separator for empty names.
        $words = preg_split('/\\s*(?:[|\\-]|\\s)\\s*/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
        $words = $words === false ? [] : $words;
        $joined = implode($separator, $words);

        return $upper ? mb_strtoupper($joined) : self::title($joined);
    }
}
