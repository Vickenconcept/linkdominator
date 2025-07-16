<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallReminder extends Model
{
    protected $table = 'call_reminder';
    
    protected $fillable = [
        'campaign',
        'requests',
        'recipients',
        'contacted',
        'replies',
        'interested',
        'not_reached',
        'industry',
        'user_id'
    ];
}
