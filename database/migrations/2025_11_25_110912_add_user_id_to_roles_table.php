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
        Schema::table('roles', function (Blueprint $table) {
            // Adds a foreign key column named 'user_id' linked to the 'id' column on the 'users' table.
            // It is set to nullable because existing roles might not have an owner.
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('guard_name') // Adjust placement as needed
                  ->constrained() // Assumes your users table is named 'users' and primary key is 'id'
                  ->onDelete('set null'); // If the user is deleted, set this field to NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropConstrainedForeignId('user_id');
        });
    }
};