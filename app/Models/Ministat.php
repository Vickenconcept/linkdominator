<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ministat extends Model
{
    protected $table = 'mini_stats';
    
    protected $fillable = [
        'connections',
        'pending_invites',
        'profile_views',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
