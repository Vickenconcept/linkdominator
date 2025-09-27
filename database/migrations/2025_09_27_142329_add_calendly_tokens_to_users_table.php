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
            $table->text('calendly_access_token')->nullable();
            $table->text('calendly_refresh_token')->nullable();
            $table->timestamp('calendly_token_expires')->nullable();
            $table->text('calendly_organization_uri')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'calendly_access_token',
                'calendly_refresh_token',
                'calendly_token_expires',
                'calendly_organization_uri'
            ]);
        });
    }
};
