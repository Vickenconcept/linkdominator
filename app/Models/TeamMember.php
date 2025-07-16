<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = [
        'member_id',
        'role',
        'team_owner_id'
    ];
}
