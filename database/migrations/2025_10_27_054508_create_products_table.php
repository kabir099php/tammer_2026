<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 30)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('barcode', 225)->nullable();
            $table->decimal('price', 24, 2)->default(0.00);
            $table->decimal('tax', 24, 2)->default(0.00);
            $table->string('tax_type', 20)->default('percent');
            $table->decimal('discount', 24, 2)->default(0.00);
            $table->string('discount_type', 20)->default('percent');
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->integer('order_count')->default(0);
            $table->double('avg_rating', 16, 14)->default(0.00000000000000);
            $table->integer('rating_count')->default(0);
            $table->string('rating', 255)->nullable();
            $table->integer('stock')->nullable()->default(0);
            $table->integer('maximum_cart_quantity')->nullable();            
            $table->longText('images')->nullable();
            $table->string('slug', 255)->nullable();

            $table->timestamps();

            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
