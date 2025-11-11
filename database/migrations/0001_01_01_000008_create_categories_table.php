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
            $table->string('image', 255)->default('def.png');
            $table->integer('parent_id')->default(0);
            $table->integer('position')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->integer('priority')->default(0);
            $table->string('slug', 255)->nullable();
            $table->tinyInteger('featured')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
