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
            // Add smart_fetch flag to control relaxed engagement threshold
            $table->boolean('smart_fetch')
                ->default(false)
                ->after('fetch_from_keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_content_preferences', function (Blueprint $table) {
            $table->dropColumn('smart_fetch');
        });
    }
};

