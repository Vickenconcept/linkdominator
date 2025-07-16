<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnLead extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'headline',
        'email',
        'lid',
        'sn_lid',
        'picture',
        'geolocation',
        'degree',
        'object_urn',
        'jobs',
        'sn_list_id'
    ];
}
