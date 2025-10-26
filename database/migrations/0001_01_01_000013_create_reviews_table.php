<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->mediumText('comment')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->integer('rating')->default(0);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('item_campaign_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('module_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->text('reply')->nullable();
            $table->string('review_id', 100)->nullable();
            $table->dateTime('replied_at')->nullable();
            $table->timestamps();

            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
