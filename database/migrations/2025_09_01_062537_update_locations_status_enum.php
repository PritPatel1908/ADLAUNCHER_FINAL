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
        Schema::table('locations', function (Blueprint $table) {
            // First, drop the existing status column
            $table->dropColumn('status');
        });

        Schema::table('locations', function (Blueprint $table) {
            // Add the status column back with the new enum constraint
            $table->tinyInteger('status')->default(1)->comment('0: Inactive, 1: Active, 2: Blocked, 3: Deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Revert back to the original status column
            $table->dropColumn('status');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1);
        });
    }
};
