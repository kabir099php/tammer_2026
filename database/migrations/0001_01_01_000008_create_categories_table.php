<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('image', 255)->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('position')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('priority')->nullable();
            $table->string('slug', 255)->nullable();
            $table->tinyInteger('featured')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
