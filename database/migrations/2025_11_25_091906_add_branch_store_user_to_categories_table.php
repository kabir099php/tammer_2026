<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Add foreign keys
            $table->foreignId('branch_id')
                ->nullable()
                ->after('id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->after('store_id')
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['store_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn(['branch_id', 'store_id', 'user_id']);
        });
    }
};
