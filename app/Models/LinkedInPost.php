<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkedInPost extends Model
{
    protected $table = 'linkedin_posts';
    
    protected $fillable = [
        'user_id',
        'content',
        'image_url',
        'video_url',
        'carousel_images',
        'post_type',
        'status',
        'scheduled_at',
        'published_at',
        'linkedin_post_id',
        'analytics_data',
        'hashtags',
        'word_count'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'analytics_data' => 'array',
        'carousel_images' => 'string', // Carousel is now PDF/PPT URL (string), not array
    ];
    
    /**
     * Get image_url attribute - handles both single URL and JSON array
     */
    public function getImageUrlAttribute($value)
    {
        // If it's null or empty, return null
        if (empty($value)) {
            return null;
        }
        
        // Try to decode as JSON
        $decoded = json_decode($value, true);
        
        // If it's valid JSON array, return the array
        if (is_array($decoded)) {
            return $decoded;
        }
        
        // Otherwise, return the original string (single URL)
        return $value;
    }
    
    /**
     * Set image_url attribute - handles both single URL and array
     */
    public function setImageUrlAttribute($value)
    {
        // If it's an array, encode to JSON
        if (is_array($value)) {
            $this->attributes['image_url'] = json_encode($value);
        } else {
            // Store as-is (single URL string or null)
            $this->attributes['image_url'] = $value;
        }
    }

    /**
     * Get the user that owns the post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for scheduled posts
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope for ready to publish posts
     */
    public function scopeReadyToPublish($query)
    {
        return $query->where('status', 'ready_to_publish');
    }

    /**
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft posts
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get engagement metrics
     */
    public function getEngagementAttribute()
    {
        $analytics = $this->analytics_data ?? [];
        return [
            'likes' => $analytics['likes'] ?? 0,
            'comments' => $analytics['comments'] ?? 0,
            'shares' => $analytics['shares'] ?? 0,
            'views' => $analytics['views'] ?? 0,
        ];
    }

    /**
     * Calculate engagement rate
     */
    public function getEngagementRateAttribute()
    {
        $engagement = $this->engagement;
        $total_engagement = $engagement['likes'] + $engagement['comments'] + $engagement['shares'];
        $views = $engagement['views'];
        
        return $views > 0 ? round(($total_engagement / $views) * 100, 2) : 0;
    }

    /**
     * Check if post is ready to be published
     */
    public function isReadyToPublish()
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_at && 
               $this->scheduled_at->isPast();
    }

    /**
     * Mark post as published
     */
    public function markAsPublished($linkedinPostId = null)
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'linkedin_post_id' => $linkedinPostId
        ]);
    }

    /**
     * Mark post as failed
     */
    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Update analytics data
     */
    public function updateAnalytics(array $analytics)
    {
        $this->update(['analytics_data' => $analytics]);
    }
}
