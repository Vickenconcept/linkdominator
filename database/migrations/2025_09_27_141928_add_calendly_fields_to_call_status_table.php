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
            $table->string('calendly_event_id')->nullable()->after('scheduled_time');
            $table->string('calendly_invitee_id')->nullable()->after('calendly_event_id');
            $table->string('calendly_meeting_url')->nullable()->after('calendly_invitee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_status', function (Blueprint $table) {
            $table->dropColumn([
                'calendly_event_id',
                'calendly_invitee_id',
                'calendly_meeting_url',
            ]);
        });
    }
};
