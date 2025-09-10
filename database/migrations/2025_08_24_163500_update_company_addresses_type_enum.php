<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Find existing check constraint dynamically
        $constraint = DB::table('sys.check_constraints as cc')
            ->join('sys.objects as o', 'cc.parent_object_id', '=', 'o.object_id')
            ->join('sys.schemas as s', 'o.schema_id', '=', 's.schema_id')
            ->where('o.name', 'company_addresses')
            ->where('cc.definition', "LIKE", "%type IN%")
            ->select('cc.name')
            ->first();

        if ($constraint) {
            DB::statement("ALTER TABLE company_addresses DROP CONSTRAINT {$constraint->name}");
        }

        // Add new constraint
        DB::statement("
            ALTER TABLE company_addresses 
            ADD CONSTRAINT CK_company_addresses_type 
            CHECK (type IN (
                'Head Office', 'Branch', 'Billing', 'Shipping', 
                'Office', 'Warehouse', 'Factory', 'Store', 'Other'
            ))
        ");
    }

    public function down(): void
    {
        // Drop new constraint
        DB::statement("ALTER TABLE company_addresses DROP CONSTRAINT CK_company_addresses_type");

        // Restore old constraint
        DB::statement("
            ALTER TABLE company_addresses 
            ADD CONSTRAINT CK_company_addresses_type_old 
            CHECK (type IN ('Head Office', 'Branch', 'Billing', 'Shipping'))
        ");
    }
};
