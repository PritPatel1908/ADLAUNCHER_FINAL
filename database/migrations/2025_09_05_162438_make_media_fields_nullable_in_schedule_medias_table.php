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
            // Make media_file and media_type nullable
            $table->string('media_file')->nullable()->change();
            $table->string('media_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_medias', function (Blueprint $table) {
            // Revert media_file and media_type to not nullable
            $table->string('media_file')->nullable(false)->change();
            $table->string('media_type')->nullable(false)->change();
        });
    }
};
