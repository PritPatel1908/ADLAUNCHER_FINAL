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
        Schema::table('schedules', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['screen_id']);

            // Drop the index that includes screen_id
            $table->dropIndex(['device_id', 'layout_id', 'screen_id']);

            // Drop the screen_id column
            $table->dropColumn('screen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Add screen_id column back
            $table->unsignedBigInteger('screen_id')->nullable();

            // Add foreign key constraint
            $table->foreign('screen_id')
                ->references('id')->on('device_screens')
                ->onDelete('no action');

            // Add the index back
            $table->index(['device_id', 'layout_id', 'screen_id']);
        });
    }
};
