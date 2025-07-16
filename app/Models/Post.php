<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'content',
        'word_counts',
        'image',
        'article',
        'save_mode',
        'schedule_time',
        'publish_status',
        'comment',
        'post_type',
        'user_id'
    ];
}
