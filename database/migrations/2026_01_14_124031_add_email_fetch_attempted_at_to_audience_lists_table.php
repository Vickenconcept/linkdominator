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
            $table->timestamp('email_fetch_attempted_at')->nullable()->after('con_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audience_lists', function (Blueprint $table) {
            $table->dropColumn('email_fetch_attempted_at');
        });
    }
};
