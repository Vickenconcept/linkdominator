<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoCommentPreference extends Model
{
    protected $fillable = [
        'user_id',
        'is_active',
        'keywords',
        'followed_accounts',
        'industries',
        'min_engagement',
        'comment_style',
        'comment_tone',
        'comment_instructions',
        'avoid_topics',
        'posting_times',
        'timezone',
        'max_comments_per_day',
        'min_time_between_comments',
        'skip_already_commented',
        'skip_posts_older_than_days',
        'only_fresh_posts',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'keywords' => 'array',
        'followed_accounts' => 'array',
        'industries' => 'array',
        'posting_times' => 'array',
        'skip_already_commented' => 'boolean',
        'only_fresh_posts' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(AutoCommentPost::class, 'preference_id');
    }
}
