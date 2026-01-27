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
        Schema::table('linkedin_posts', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('linkedin_posts', 'video_url')) {
                $table->string('video_url', 500)->nullable()->after('image_url');
            }
            
            if (!Schema::hasColumn('linkedin_posts', 'carousel_images')) {
                $table->string('carousel_images', 500)->nullable()->after('video_url');
            }
        });
        
        // Update status enum and image_url length (separate alter)
        Schema::table('linkedin_posts', function (Blueprint $table) {
            // Update status enum to include 'ready_to_publish' status
            $table->enum('status', ['draft', 'scheduled', 'ready_to_publish', 'published', 'failed'])->default('draft')->change();
            
            // Make image_url longer to support JSON arrays of URLs
            $table->string('image_url', 2000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linkedin_posts', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'carousel_images']);
            
            // Revert status enum
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft')->change();
            
            // Revert image_url length
            $table->string('image_url', 255)->nullable()->change();
        });
    }
};
