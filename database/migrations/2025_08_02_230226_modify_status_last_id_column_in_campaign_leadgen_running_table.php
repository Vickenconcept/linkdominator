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
        Schema::table('campaign_leadgen_running', function (Blueprint $table) {
            // Change status_last_id from integer to string to support text values like 'invite_sent'
            $table->string('status_last_id', 50)->default('0')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_leadgen_running', function (Blueprint $table) {
            // Revert back to integer
            $table->integer('status_last_id')->default(0)->change();
        });
    }
};
