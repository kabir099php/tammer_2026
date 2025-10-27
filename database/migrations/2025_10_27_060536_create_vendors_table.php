<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('f_name', 100);
            $table->string('l_name', 100)->nullable();
            $table->string('phone', 20)->index();
            $table->string('crn', 225)->nullable();
            $table->string('email', 100)->index();
            $table->integer('currency_id')->default(1);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 100);
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('branch', 255)->nullable();
            $table->string('account_no', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->tinyInteger('status')->nullable()->default(1);
            $table->string('vat', 225)->nullable();
            $table->integer('is_not_vat')->default(1);
            $table->longText('detail_page_footer')->default('You can edit this text from vendor panel');
            $table->string('firebase_token', 255)->nullable();
            $table->string('auth_token', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
