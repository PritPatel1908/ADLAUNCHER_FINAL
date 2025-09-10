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
            // Make layout_id and screen_id nullable
            $table->unsignedBigInteger('layout_id')->nullable()->change();
            $table->unsignedBigInteger('screen_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Revert layout_id and screen_id to not nullable
            $table->unsignedBigInteger('layout_id')->nullable(false)->change();
            $table->unsignedBigInteger('screen_id')->nullable(false)->change();
        });
    }
};
