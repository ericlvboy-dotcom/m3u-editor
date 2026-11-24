<?php

namespace Tests\Unit;

use App\Services\PlaylistSortPrecedence;
use PHPUnit\Framework\TestCase;

class PlaylistSortPrecedenceTest extends TestCase
{
    public function test_newer_explicit_merged_sort_supersedes_groups_sort(): void
    {
        $groups = ['scope' => 'groups_channels', 'order' => 'manual', 'revision' => 4];
        $merged = ['scope' => 'merged_playlist', 'order' => 'az', 'revision' => 5];

        $this->assertSame($merged, PlaylistSortPrecedence::latest($groups, $merged));
    }

    public function test_later_groups_sort_supersedes_merged_sort(): void
    {
        $merged = ['scope' => 'merged_playlist', 'order' => 'za', 'revision' => 5];
        $groups = ['scope' => 'groups_channels', 'order' => 'manual', 'revision' => 6];

        $this->assertSame($groups, PlaylistSortPrecedence::latest($merged, $groups));
    }

    public function test_unrelated_save_does_not_change_active_sort(): void
    {
        $active = ['scope' => 'groups_channels', 'order' => 'manual', 'revision' => 8];

        $this->assertSame($active, PlaylistSortPrecedence::latest($active, null));
    }

    public function test_equal_revision_keeps_existing_sort(): void
    {
        $active = ['scope' => 'groups_channels', 'order' => 'manual', 'revision' => 8];
        $other = ['scope' => 'merged_playlist', 'order' => 'az', 'revision' => 8];

        $this->assertSame($active, PlaylistSortPrecedence::latest($active, $other));
    }
}
