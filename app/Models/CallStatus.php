<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallStatus extends Model
{
    protected $table = 'call_status';
    
    protected $fillable = [
        'recipient',
        'profile',
        'sequence',
        'call_status',
        'user_id',
        'company',
        'industry',
        'job_title',
        'location',
        'scheduled_time',
        'calendar_link',
        'meeting_link',
        'timezone',
        'original_message',
        'conversation_history',
        'ai_analysis',
        'lead_category',
        'lead_score',
        'linkedin_profile_url',
        'connection_id',
        'conversation_urn_id',
        'campaign_id',
        'campaign_name',
        'reminder_16_24_sent',
        'reminder_2_hours_sent',
        'reminder_10_40_min_sent',
        'last_interaction_at',
        'interaction_count'
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'last_interaction_at' => 'datetime',
        'ai_analysis' => 'array',
        'reminder_16_24_sent' => 'boolean',
        'reminder_2_hours_sent' => 'boolean',
        'reminder_10_40_min_sent' => 'boolean',
    ];

    /**
     * Get the user that owns the call status
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaign that owns the call status
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Scope for calls that need reminders
     */
    public function scopeNeedsReminder($query, $reminderType)
    {
        return $query->where('call_status', 'scheduled')
            ->where('scheduled_time', '>', now())
            ->where("reminder_{$reminderType}_sent", false);
    }

    /**
     * Scope for calls by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('call_status', $status);
    }

    /**
     * Scope for calls by lead category
     */
    public function scopeByLeadCategory($query, $category)
    {
        return $query->where('lead_category', $category);
    }
}
