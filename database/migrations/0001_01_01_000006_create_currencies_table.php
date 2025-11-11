<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('country', 255)->nullable();
            $table->string('currency_code', 255)->nullable();
            $table->string('currency_symbol', 255)->nullable();
            $table->decimal('exchange_rate', 8, 2)->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
