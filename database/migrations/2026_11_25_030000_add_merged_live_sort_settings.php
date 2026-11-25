<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merged_playlists', function (Blueprint $table): void {
            $table->string('live_group_sort')->default('default');
            $table->string('live_channel_sort')->default('default');
        });
    }

    public function down(): void
    {
        Schema::table('merged_playlists', fn (Blueprint $table) => $table->dropColumn(['live_group_sort', 'live_channel_sort']));
    }
};
