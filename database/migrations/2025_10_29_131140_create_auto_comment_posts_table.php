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
        Schema::create('auto_comment_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preference_id');
            $table->unsignedBigInteger('user_id');
            
            // Post information
            $table->string('post_urn')->unique(); // LinkedIn post URN
            $table->string('post_url')->nullable();
            $table->text('post_content')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_headline')->nullable();
            $table->string('author_profile_url')->nullable();
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('shares')->default(0);
            $table->dateTime('post_date')->nullable();
            
            // AI Generated Comment
            $table->text('generated_comment')->nullable();
            $table->dateTime('comment_generated_at')->nullable();
            
            // Posting status
            $table->enum('status', ['pending', 'scheduled', 'posted', 'failed', 'skipped'])->default('pending');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->string('comment_urn')->nullable(); // LinkedIn comment URN after posting
            $table->text('error_message')->nullable();
            
            // Match reasons
            $table->text('matched_keywords')->nullable(); // Which keywords matched
            $table->string('match_type')->nullable(); // 'keyword', 'followed_account', 'industry'
            
            $table->timestamps();
            
            $table->foreign('preference_id')->references('id')->on('auto_comment_preferences')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'scheduled_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_comment_posts');
    }
};
