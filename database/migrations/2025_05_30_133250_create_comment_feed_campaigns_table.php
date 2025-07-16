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
        Schema::create('comment_feed_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('linkedin_profile_url')->nullable();
            $table->string('campaign_type')->nullable();
            $table->text('keyword_list')->nullable();
            $table->text('profile_list')->nullable();
            $table->string('ai_commenter')->nullable();
            $table->string('ai_comment_style')->nullable();
            $table->string('ai_comment_type')->nullable();
            $table->text('product_name_description')->nullable();
            $table->text('product_unique_selling_point')->nullable();
            $table->text('persona_description')->nullable();
            $table->text('what_ai_need_todo')->nullable();
            $table->text('what_ai_should_avoid')->nullable();
            $table->text('tone_style')->nullable();
            $table->unSignedBigInteger('user_id');
            $table->string('custom_webhook')->nullable();
            $table->string('campaign_name')->nullable();
            $table->bigInteger('max_comment_per_day_campaign')->default(50);
            $table->bigInteger('max_comment_per_profile_day')->default(1);
            $table->bigInteger('max_comment_per_profile_week')->default(7);
            $table->bigInteger('max_comment_per_profile_month')->default(30);
            $table->bigInteger('skip_post_older_than')->default(90);
            $table->string('status')->nullable();
            $table->bigInteger('total_post_found')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_feed_campaigns');
    }
};
