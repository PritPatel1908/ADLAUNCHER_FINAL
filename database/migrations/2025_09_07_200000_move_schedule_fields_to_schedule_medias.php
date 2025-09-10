<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedule_medias', function (Blueprint $table) {
            $table->dateTime('schedule_start_date_time')->nullable()->after('screen_id');
            $table->dateTime('schedule_end_date_time')->nullable()->after('schedule_start_date_time');
            $table->boolean('play_forever')->default(false)->after('schedule_end_date_time');
        });

        // Drop columns from schedules now that they live on schedule_medias
        Schema::table('schedules', function (Blueprint $table) {
            // Drop composite index if it exists (SQL Server requires dropping dependent indexes first)
            try {
                $table->dropIndex('schedules_schedule_start_date_time_schedule_end_date_time_index');
            } catch (\Throwable $e) {
                // Ignore if index does not exist
            }
            if (Schema::hasColumn('schedules', 'schedule_start_date_time')) {
                $table->dropColumn('schedule_start_date_time');
            }
            if (Schema::hasColumn('schedules', 'schedule_end_date_time')) {
                $table->dropColumn('schedule_end_date_time');
            }
            if (Schema::hasColumn('schedules', 'play_forever')) {
                $table->dropColumn('play_forever');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate columns on schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->dateTime('schedule_start_date_time')->nullable()->after('schedule_name');
            $table->dateTime('schedule_end_date_time')->nullable()->after('schedule_start_date_time');
            $table->boolean('play_forever')->default(false)->after('screen_id');
            // Optionally recreate the composite index
            $table->index(['schedule_start_date_time', 'schedule_end_date_time']);
        });

        // Drop columns from schedule_medias
        Schema::table('schedule_medias', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_medias', 'schedule_start_date_time')) {
                $table->dropColumn('schedule_start_date_time');
            }
            if (Schema::hasColumn('schedule_medias', 'schedule_end_date_time')) {
                $table->dropColumn('schedule_end_date_time');
            }
            if (Schema::hasColumn('schedule_medias', 'play_forever')) {
                $table->dropColumn('play_forever');
            }
        });
    }
};
