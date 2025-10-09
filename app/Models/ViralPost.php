<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViralPost extends Model
{
    protected $fillable = [
        'user_id',
        'author_name',
        'author_headline',
        'author_profile_url',
        'author_image_url',
        'content',
        'post_url',
        'linkedin_post_id',
        'likes',
        'comments',
        'shares',
        'views',
        'engagement_rate',
        'post_type',
        'images',
        'video_url',
        'post_date',
        'category',
        'tags',
        'is_favorite',
        'is_public',
        'saved_at'
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array',
        'post_date' => 'datetime',
        'saved_at' => 'datetime',
        'is_favorite' => 'boolean',
        'is_public' => 'boolean',
        'engagement_rate' => 'decimal:2'
    ];

    /**
     * Get the user that saved this viral post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for high engagement posts
     */
    public function scopeHighEngagement($query, $threshold = 5.0)
    {
        return $query->where('engagement_rate', '>=', $threshold);
    }

    /**
     * Scope for viral posts (very high engagement)
     */
    public function scopeViral($query)
    {
        return $query->where('engagement_rate', '>=', 10.0)
                    ->orWhere('likes', '>=', 1000);
    }

    /**
     * Scope for favorites
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope for public posts (shared with community)
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by date range
     */
    public function scopeRecentDays($query, $days = 30)
    {
        return $query->where('post_date', '>=', now()->subDays($days));
    }

    /**
     * Calculate total engagement
     */
    public function getTotalEngagementAttribute()
    {
        return $this->likes + $this->comments + $this->shares;
    }

    /**
     * Get formatted engagement rate
     */
    public function getFormattedEngagementRateAttribute()
    {
        return number_format($this->engagement_rate, 2) . '%';
    }

    /**
     * Calculate engagement score from raw metrics
     */
    public static function calculateEngagementRate($likes, $comments, $shares, $views)
    {
        if ($views == 0) {
            // Estimate views if not available (conservative estimate)
            $views = max(($likes + $comments + $shares) * 10, 100);
        }
        
        $totalEngagement = $likes + ($comments * 2) + ($shares * 3); // Weight comments and shares higher
        return round(($totalEngagement / $views) * 100, 2);
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite()
    {
        $this->update(['is_favorite' => !$this->is_favorite]);
    }

    /**
     * Extract hashtags from content
     */
    public function getHashtagsAttribute()
    {
        preg_match_all('/#\w+/', $this->content, $matches);
        return $matches[0] ?? [];
    }

    /**
     * Get content preview (first 150 characters)
     */
    public function getPreviewAttribute()
    {
        return \Str::limit($this->content, 150);
    }
}
