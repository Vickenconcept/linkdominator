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
        // First normalize any old enum values so we can safely change the column definition
        DB::table('user_content_preferences')
            ->whereIn('date_range', ['past-2-weeks', 'past-3-weeks'])
            ->update(['date_range' => 'past-month']);

        Schema::table('user_content_preferences', function (Blueprint $table) {
            // Align DB enum with the new allowed values in the app
            $table->enum('date_range', [
                'past-24-hours',
                'past-week',
                'past-month',
                'past-year',
                'any-time',
            ])->default('past-month')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_content_preferences', function (Blueprint $table) {
            // Restore original enum definition
            $table->enum('date_range', [
                'past-week',
                'past-month',
                'past-2-weeks',
                'past-3-weeks',
                'any-time',
            ])->default('past-month')->change();
        });
    }
};

