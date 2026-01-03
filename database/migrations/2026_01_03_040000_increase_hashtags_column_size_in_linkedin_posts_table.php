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
            // Change hashtags from VARCHAR(255) to TEXT to support longer hashtag strings
            $table->text('hashtags')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linkedin_posts', function (Blueprint $table) {
            // Revert back to string (VARCHAR(255))
            $table->string('hashtags')->nullable()->change();
        });
    }
};

