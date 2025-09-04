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
            // Lead context data
            $table->string('company')->nullable()->after('profile');
            $table->string('industry')->nullable()->after('company');
            $table->string('job_title')->nullable()->after('industry');
            $table->string('location')->nullable()->after('job_title');
            
            // Call scheduling data
            $table->timestamp('scheduled_time')->nullable()->after('call_status');
            $table->string('calendar_link')->nullable()->after('scheduled_time');
            $table->string('meeting_link')->nullable()->after('calendar_link');
            $table->string('timezone')->nullable()->after('meeting_link');
            
            // AI and conversation data
            $table->text('original_message')->nullable()->after('timezone');
            $table->text('conversation_history')->nullable()->after('original_message');
            $table->json('ai_analysis')->nullable()->after('conversation_history');
            $table->string('lead_category')->nullable()->after('ai_analysis');
            $table->integer('lead_score')->nullable()->after('lead_category');
            
            // LinkedIn specific data
            $table->string('linkedin_profile_url')->nullable()->after('lead_score');
            $table->string('connection_id')->nullable()->after('linkedin_profile_url');
            $table->string('conversation_urn_id')->nullable()->after('connection_id');
            
            // Campaign tracking
            $table->unsignedBigInteger('campaign_id')->nullable()->after('conversation_urn_id');
            $table->string('campaign_name')->nullable()->after('campaign_id');
            
            // Reminder tracking
            $table->boolean('reminder_16_24_sent')->default(false)->after('campaign_name');
            $table->boolean('reminder_2_hours_sent')->default(false)->after('reminder_16_24_sent');
            $table->boolean('reminder_10_40_min_sent')->default(false)->after('reminder_2_hours_sent');
            
            // Status tracking
            $table->timestamp('last_interaction_at')->nullable()->after('reminder_10_40_min_sent');
            $table->integer('interaction_count')->default(0)->after('last_interaction_at');
            
            // Add indexes for performance
            $table->index(['user_id', 'call_status']);
            $table->index(['scheduled_time']);
            $table->index(['campaign_id']);
            $table->index(['lead_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_status', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'call_status']);
            $table->dropIndex(['scheduled_time']);
            $table->dropIndex(['campaign_id']);
            $table->dropIndex(['lead_category']);
            
            $table->dropColumn([
                'company', 'industry', 'job_title', 'location',
                'scheduled_time', 'calendar_link', 'meeting_link', 'timezone',
                'original_message', 'conversation_history', 'ai_analysis',
                'lead_category', 'lead_score', 'linkedin_profile_url',
                'connection_id', 'conversation_urn_id', 'campaign_id',
                'campaign_name', 'reminder_16_24_sent', 'reminder_2_hours_sent',
                'reminder_10_40_min_sent', 'last_interaction_at', 'interaction_count'
            ]);
        });
    }
};