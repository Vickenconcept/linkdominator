<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamInvite extends Model
{
    protected $fillable = [
        'email',
        'role',
        'invited_by'
    ];

    public function invitedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'invited_by');
    }
}
