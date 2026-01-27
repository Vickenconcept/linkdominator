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
        Schema::table('user_content_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('user_content_preferences', 'date_range')) {
                $table->enum('date_range', ['past-week', 'past-month', 'past-2-weeks', 'past-3-weeks', 'any-time'])
                      ->default('past-month')
                      ->after('min_engagement');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_content_preferences', function (Blueprint $table) {
            $table->dropColumn('date_range');
        });
    }
};
