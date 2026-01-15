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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('daily_profile_email_scraping_count')->default(0)->after('calendly_organization_uri');
            $table->date('daily_profile_email_scraping_reset_at')->nullable()->after('daily_profile_email_scraping_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'daily_profile_email_scraping_count',
                'daily_profile_email_scraping_reset_at'
            ]);
        });
    }
};
