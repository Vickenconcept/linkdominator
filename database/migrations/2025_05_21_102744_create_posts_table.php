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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable();
            $table->integer('word_counts')->default(0);
            $table->string('image')->nullable();
            $table->text('article')->nullable();
            $table->enum('save_mode', ['schedule','instant','draft'])->nullable();
            $table->datetime('schedule_time')->nullable();
            $table->enum('publish_status', ['published','failed','scheduled','draft','pending'])->nullable();
            $table->text('comment')->nullable();
            $table->enum('post_type', ['text only','article only','image only','text with image','text with article']);
            $table->unSignedBigInteger('user_id');
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
