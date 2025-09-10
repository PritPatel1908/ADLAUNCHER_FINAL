<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedule_medias', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_medias', 'duration_seconds')) {
                $table->integer('duration_seconds')->nullable()->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_medias', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_medias', 'duration_seconds')) {
                $table->dropColumn('duration_seconds');
            }
        });
    }
};
