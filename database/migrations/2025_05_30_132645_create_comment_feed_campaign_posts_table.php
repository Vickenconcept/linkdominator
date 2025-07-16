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
        Schema::create('comment_feed_campaign_post', function (Blueprint $table) {
            $table->id();
            $table->unSignedBigInteger('campaign_id');
            $table->string('num_comments')->nullable();
            $table->string('num_likes')->nullable();
            $table->string('num_shares')->nullable();
            $table->string('post_type')->nullable();
            $table->text('post_url')->nullable();
            $table->dateTime('posted')->nullable();
            $table->text('poster_linkedin_url')->nullable();
            $table->string('poster_name')->nullable();
            $table->string('poster_title')->nullable();
            $table->longText('post')->nullable();
            $table->string('urn')->nullable();
            $table->longText('comment')->nullable();
            $table->string('comment_status')->nullable();
            $table->text('failed_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_feed_campaign_post');
    }
};
