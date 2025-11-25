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
            'dashboard',

            // User Management
            'users',
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
        $vendorRole->givePermissionTo(['dashboard','products','branches']); 

        
        // Employee can the dashboard
        $employeeRole->givePermissionTo(['dashboard']);

        
        // Basic User can only the dashboard
        $userRole->givePermissionTo('dashboard');
    }
}