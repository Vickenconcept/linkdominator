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
        Schema::create('call_status', function (Blueprint $table) {
            // Lead context data
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('recipient')->nullable();
            $table->string('profile')->nullable();
            $table->string('company')->nullable();
            $table->string('industry')->nullable();
            $table->string('job_title')->nullable();
            $table->string('location')->nullable();
            $table->string('sequence')->nullable();
            $table->string('call_status')->nullable();
            $table->text('pending_message')->nullable();

            $table->timestamp('scheduled_send_at')->nullable();
            $table->string('lead_category')->nullable()->index();
            $table->integer('lead_score')->nullable();
            $table->string('linkedin_profile_url')->nullable();
            
            // Call scheduling data
            $table->timestamp('scheduled_time')->nullable();
            $table->string('calendar_link')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('timezone')->nullable();
            
            // AI and conversation data
            $table->text('original_message')->nullable();
            $table->text('conversation_history')->nullable();
            $table->json('ai_analysis')->nullable();
           
            
            // LinkedIn specific data
            $table->string('connection_id')->nullable()->index();
            $table->string('conversation_urn_id')->nullable();
            
            // Campaign tracking
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->string('campaign_name')->nullable();
            
            // Reminder tracking
            $table->boolean('reminder_16_24_sent')->default(false);
            $table->boolean('reminder_2_hours_sent')->default(false);
            $table->boolean('reminder_10_40_min_sent')->default(false);
            
            // Status tracking
            $table->timestamp('last_interaction_at')->nullable();
            $table->integer('interaction_count')->default(0);

            $table->string('calendly_event_id')->nullable();
            $table->string('calendly_invitee_id')->nullable()->index();
            $table->string('calendly_meeting_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_status');
    }
};