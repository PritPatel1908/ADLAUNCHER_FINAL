<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQL Server, we need to handle the unique constraint differently
        // SQL Server doesn't allow multiple NULL values in a unique index
        // We'll drop the existing unique constraint to allow multiple NULL values

        // Drop the existing unique constraint
        DB::statement("DROP INDEX users_employee_id_unique ON users");

        // Note: The employee_id field will still be nullable but not unique
        // This allows multiple users to have NULL employee_id values
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the original unique constraint
        // Note: This may fail if there are multiple NULL values
        try {
            DB::statement("CREATE UNIQUE INDEX users_employee_id_unique ON users (employee_id)");
        } catch (\Exception $e) {
            // If it fails, we'll just log it and continue
            Log::warning('Could not recreate unique constraint on employee_id field: ' . $e->getMessage());
        }
    }
};
