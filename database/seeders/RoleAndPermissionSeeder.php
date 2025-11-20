<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Define Granular User Permissions ---
        $permissions = [
            // General
            'view dashboard',

            // User Management
            'view_users',    // Corresponds to canViewAny (Resource visibility)
            'create_users',  // Corresponds to canCreate (Create button/page)
            'update_users',  // Corresponds to canUpdate (Edit action/page)
            'delete_users',  // Corresponds to canDelete (Delete action)
            'delete_bulk_users', // Corresponds to Bulk Delete action
            'categroy', // Corresponds to Bulk Delete action
            'branches', // Corresponds to Bulk Delete action
            'products', // Corresponds to Bulk Delete action
            'stores', // Corresponds to Bulk Delete action
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Remove the old generic permission if it exists
        // Permission::where('name', 'manage users')->delete(); 

        // --- Create Roles ---
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // --- Assign Permissions ---

        // Super Admin gets all user management permissions
        $superAdminRole->givePermissionTo($permissions);
        

        
        // Vendor can view the dashboard and maybe view users (optional)
        $vendorRole->givePermissionTo(['view dashboard','products','stores','branches']); 

        
        // Employee can view the dashboard
        $employeeRole->givePermissionTo(['view dashboard']);

        
        // Basic User can only view the dashboard
        $userRole->givePermissionTo('view dashboard');
    }
}