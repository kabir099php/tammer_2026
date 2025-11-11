<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->double('order_amount', 24, 2)->default(0);
            $table->double('vatpr')->default(0);
            $table->double('vatamt')->default(0);
            $table->longText('payment_token')->nullable();
            $table->string('payment_type', 225)->nullable();
            $table->string('payement_gateway_status', 225)->default('Pending');
            $table->string('payment_status', 255)->default('unpaid');
            $table->string('order_status', 255)->default('pending');
            $table->decimal('total_tax_amount', 24, 2)->default(0.00);
            $table->string('payment_method', 30)->nullable();
            $table->unsignedBigInteger('status')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->double('tax_percentage', 24, 3)->nullable();
            $table->double('partially_paid_amount', 23, 3)->default(0.000);
            $table->tinyInteger('is_guest')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
