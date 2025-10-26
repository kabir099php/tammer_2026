<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('device_id', 255)->nullable();
            $table->string('terminal_id', 255)->nullable()->index();
            $table->string('name', 255)->nullable();
            $table->string('code', 255)->nullable();
            $table->string('connection_status', 255)->nullable();
            $table->integer('store_id');
            $table->integer('vendor_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->time('deleted_at')->nullable(); // matches your schema
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_devices');
    }
};
