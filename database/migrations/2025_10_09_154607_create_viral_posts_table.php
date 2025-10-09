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
        Schema::create('viral_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Post author information
            $table->string('author_name');
            $table->string('author_headline')->nullable();
            $table->string('author_profile_url')->nullable();
            $table->string('author_image_url')->nullable();
            
            // Post content
            $table->text('content');
            $table->string('post_url')->nullable();
            $table->string('linkedin_post_id')->nullable()->unique();
            
            // Engagement metrics
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('views')->default(0);
            $table->decimal('engagement_rate', 5, 2)->default(0);
            
            // Post metadata
            $table->enum('post_type', ['text', 'image', 'carousel', 'video', 'article'])->default('text');
            $table->json('images')->nullable(); // For image/carousel posts
            $table->string('video_url')->nullable();
            $table->timestamp('post_date')->nullable(); // When it was posted on LinkedIn
            
            // Categorization
            $table->string('category')->nullable(); // e.g., 'marketing', 'sales', 'tech'
            $table->json('tags')->nullable(); // Keywords/hashtags extracted
            
            // User actions
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_public')->default(false); // Share with other users?
            $table->timestamp('saved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'engagement_rate']);
            $table->index(['category']);
            $table->index(['post_date']);
            $table->index(['engagement_rate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viral_posts');
    }
};
