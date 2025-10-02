<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include 'ready_to_publish'
        DB::statement("ALTER TABLE linkedin_posts MODIFY COLUMN status ENUM('draft', 'scheduled', 'published', 'failed', 'ready_to_publish') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE linkedin_posts MODIFY COLUMN status ENUM('draft', 'scheduled', 'published', 'failed') DEFAULT 'draft'");
    }
};