<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->bigIncrements('id');

            
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->string('phone', 20)->index();       
            $table->string('email', 100)->nullable();   
            $table->string('logo', 255)->nullable();    
            $table->string('latitude', 255)->nullable();
            $table->string('longitude', 255)->nullable();
            $table->text('address')->nullable();        
            $table->tinyInteger('status')->default(1);  
            $table->string('slug', 255)->nullable();    
            $table->timestamps();                       
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
