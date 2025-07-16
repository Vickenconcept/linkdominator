<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentFeedCampaign extends Model
{
    protected $fillable = [
        'linkedin_profile_url',
        'campaign_type',
        'keyword_list',
        'profile_list',
        'ai_commenter',
        'ai_comment_style',
        'ai_comment_type',
        'product_name_description',
        'product_unique_selling_point',
        'persona_description',
        'what_ai_need_todo',
        'what_ai_should_avoid',
        'tone_style',
        'user_id',
        'custom_webhook',
        'campaign_name',
        'max_comment_per_day_campaign',
        'max_comment_per_profile_day',
        'max_comment_per_profile_week',
        'max_comment_per_profile_month',
        'skip_post_older_than',
        'status',
        'total_post_found',
    ];
}
