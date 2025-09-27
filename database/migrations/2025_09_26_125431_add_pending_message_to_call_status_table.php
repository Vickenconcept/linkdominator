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
        Schema::table('call_status', function (Blueprint $table) {
            $table->text('pending_message')->nullable()->after('call_status');
            $table->timestamp('scheduled_send_at')->nullable()->after('pending_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_status', function (Blueprint $table) {
            $table->dropColumn(['pending_message', 'scheduled_send_at']);
        });
    }
};
