<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoCommentPost extends Model
{
    protected $fillable = [
        'preference_id',
        'user_id',
        'post_urn',
        'post_url',
        'post_content',
        'author_name',
        'author_headline',
        'author_profile_url',
        'likes',
        'comments',
        'shares',
        'post_date',
        'generated_comment',
        'comment_generated_at',
        'status',
        'scheduled_at',
        'posted_at',
        'comment_urn',
        'error_message',
        'matched_keywords',
        'match_type',
    ];

    protected $casts = [
        'post_date' => 'datetime',
        'comment_generated_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function preference(): BelongsTo
    {
        return $this->belongsTo(AutoCommentPreference::class, 'preference_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
