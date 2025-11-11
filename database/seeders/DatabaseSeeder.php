<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Make sure to import your User model

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
        // 1. Create a default Admin User for the Filament Panel
        if (! User::where('email', 'admin@example.com')->exists()) {
           $adminUser =  User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // Use 'password' or a secure password
            ]);
            $adminUser->assignRole('super admin');
            
        }

    }
}