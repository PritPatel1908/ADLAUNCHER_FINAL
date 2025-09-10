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
            // Add screen_id column
            $table->unsignedBigInteger('screen_id')->nullable()->after('schedule_id');

            // Add foreign key constraint
            $table->foreign('screen_id')
                ->references('id')->on('device_screens')
                ->onDelete('no action');

            // Add index for screen_id
            $table->index(['screen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_medias', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['screen_id']);

            // Drop the index
            $table->dropIndex(['screen_id']);

            // Drop the screen_id column
            $table->dropColumn('screen_id');
        });
    }
};
