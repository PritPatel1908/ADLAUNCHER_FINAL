<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class SingleRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['role_name' => 'Administrator'],
            [
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );

        RolePermission::firstOrCreate(
            [
                'role_id' => $role->id,
                'modules' => 'dashboard',
            ],
            [
                'view' => true,
                'create' => true,
                'edit' => true,
                'delete' => true,
                'import' => false,
                'export' => false,
                'manage_columns' => true,
            ]
        );
    }
}
