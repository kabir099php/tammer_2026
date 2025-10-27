<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id(); // id bigint(20) unsigned auto_increment
            $table->string('translationable_type'); // varchar(255)
            $table->unsignedBigInteger('translationable_id'); // bigint(20) unsigned
            $table->string('locale')->index(); // varchar(255) index
            $table->string('key')->nullable(); // varchar(255) nullable
            $table->text('value')->nullable(); // text nullable
            $table->timestamps(); // created_at & updated_at

            
            $table->index(['translationable_id', 'translationable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
