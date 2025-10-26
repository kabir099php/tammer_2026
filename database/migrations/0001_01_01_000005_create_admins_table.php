<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('f_name', 100)->nullable();
            $table->string('l_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->unique();   
            $table->string('image', 100)->nullable();
            $table->string('password', 100);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
