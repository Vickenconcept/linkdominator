<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallReminderMessage extends Model
{
    protected $fillable = [
        'call_reminder_id',
        '16_24_hours_before_status',
        '16_24_hours_before_message',
        'couple_hours_before_status',
        'couple_hours_before_message',
        '10_40_minutes_before_status',
        '10_40_minutes_before_message'
    ];

    
}
