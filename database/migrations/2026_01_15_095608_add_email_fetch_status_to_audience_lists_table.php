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
        Schema::table('audience_lists', function (Blueprint $table) {
            $table->string('email_fetch_status', 20)->nullable()->after('email_fetch_attempted_at')->comment('pending, completed, or NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audience_lists', function (Blueprint $table) {
            $table->dropColumn('email_fetch_status');
        });
    }
};
