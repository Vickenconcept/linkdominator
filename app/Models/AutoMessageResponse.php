<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoMessageResponse extends Model
{
    protected $table = 'auto_message_response';
    
    protected $fillable = [
        'message_type',
        'message_keywords',
        'total_endorse_skills',
        'message_body',
        'attachement',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
