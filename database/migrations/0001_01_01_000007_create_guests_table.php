<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contact_name', 225)->nullable();
            $table->string('contact_email', 225)->nullable();
            $table->string('contact_number', 225)->nullable();
            $table->timestamps(); // creates created_at and updated_at (nullable)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
