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
        Schema::table('integrations', function (Blueprint $table) {
            $table->text('linkedin_session_cookie')->nullable()->after('access_token');
            $table->text('linkedin_user_agent')->nullable()->after('linkedin_session_cookie');
            $table->timestamp('linkedin_session_verified_at')->nullable()->after('linkedin_user_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn([
                'linkedin_session_cookie',
                'linkedin_user_agent',
                'linkedin_session_verified_at',
            ]);
        });
    }
};
