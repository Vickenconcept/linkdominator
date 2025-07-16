<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentFeedCampaignPost extends Model
{
    protected $table = 'comment_feed_campaign_post';

    protected $fillable = [
        'campaign_id',
        'num_comments',
        'num_likes',
        'num_shares',
        'post_type',
        'post_url',
        'posted',
        'poster_linkedin_url',
        'poster_name',
        'poster_title',
        'post',
        'urn',
        'comment',
        'comment_status',
        'failed_reason',
    ];
}
