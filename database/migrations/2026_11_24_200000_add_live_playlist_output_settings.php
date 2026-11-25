<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['playlists', 'merged_playlists'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('live_group_case')->default('original');
                $table->string('live_channel_case')->default('original');
            });
        }

        Schema::table('playlists', function (Blueprint $table): void {
            $table->string('live_group_prefix')->nullable();
        });

        DB::table('playlists')->update([
            'auto_sort' => false,
            'auto_sort_groups' => false,
            'auto_sort_categories' => false,
            'sort_by_channel_number' => false,
        ]);
    }

    public function down(): void
    {
        Schema::table('playlists', fn (Blueprint $table) => $table->dropColumn('live_group_prefix'));

        foreach (['playlists', 'merged_playlists'] as $tableName) {
            Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn(['live_group_case', 'live_channel_case']));
        }
    }
};
