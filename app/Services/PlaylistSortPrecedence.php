<?php

namespace App\Services;

/**
 * Resolves competing explicit group-order actions without using unrelated model
 * updated_at timestamps. Persistence and export integration are handled by callers.
 */
final class PlaylistSortPrecedence
{
    /**
     * @param array{scope: string, order: string, revision: int}|null $current
     * @param array{scope: string, order: string, revision: int}|null $candidate
     * @return array{scope: string, order: string, revision: int}|null
     */
    public static function latest(?array $current, ?array $candidate): ?array
    {
        if ($candidate === null) {
            return $current;
        }

        if ($current === null) {
            return $candidate;
        }

        // Revision must be issued only for an explicitly applied sorting action.
        // Equal revisions preserve the already-active choice deterministically.
        return $candidate['revision'] > $current['revision'] ? $candidate : $current;
    }
}
