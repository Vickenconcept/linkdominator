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
        Schema::create('auto_comment_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_active')->default(true);
            
            // Post search preferences
            $table->json('keywords')->nullable(); // Array of keywords/topics
            $table->json('followed_accounts')->nullable(); // Array of LinkedIn profile URLs or URNs to monitor
            $table->json('industries')->nullable(); // Industries to focus on
            $table->integer('min_engagement')->default(50); // Minimum likes/comments to consider
            
            // AI Comment preferences
            $table->string('comment_style')->default('professional'); // professional, casual, friendly, etc.
            $table->string('comment_tone')->default('helpful'); // helpful, engaging, supportive, etc.
            $table->text('comment_instructions')->nullable(); // Custom instructions for AI
            $table->text('avoid_topics')->nullable(); // Topics to avoid in comments
            
            // Posting schedule
            $table->json('posting_times')->nullable(); // Array of preferred hours [9, 14, 18]
            $table->string('timezone')->nullable(); // User's timezone
            $table->integer('max_comments_per_day')->default(10);
            $table->integer('min_time_between_comments')->default(60); // Minutes
            
            // Filters
            $table->boolean('skip_already_commented')->default(true);
            $table->integer('skip_posts_older_than_days')->default(7);
            $table->boolean('only_fresh_posts')->default(true); // Only comment on posts < 24 hours
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_comment_preferences');
    }
};
