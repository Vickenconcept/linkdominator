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
        Schema::create('user_content_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Industry/Niche preferences
            $table->json('industries')->nullable(); // ['tech', 'healthcare', 'real estate', 'finance']
            $table->json('topics')->nullable(); // ['AI', 'leadership', 'marketing', 'sales']
            $table->json('keywords')->nullable(); // Custom keywords user wants to track
            
            // Content preferences
            $table->integer('min_engagement')->default(100); // Minimum likes to consider viral
            $table->json('preferred_post_types')->nullable(); // ['text', 'video', 'carousel']
            
            // Date range preference - CRITICAL for finding posts with high engagement
            $table->enum('date_range', ['past-week', 'past-month', 'past-2-weeks', 'past-3-weeks', 'any-time'])->default('past-month');
            
            // Creator preferences (optional - user can follow specific creators)
            $table->json('favorite_creators')->nullable(); // URLs of creators they like
            
            // Search behavior
            $table->boolean('fetch_from_creators')->default(false); // Enable creator-based fetching
            $table->boolean('fetch_from_keywords')->default(true); // Enable keyword-based fetching
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_content_preferences');
    }
};
