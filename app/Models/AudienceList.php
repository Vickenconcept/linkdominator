<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudienceList extends Model
{
    protected $fillable = [
        'audience_id',
        'con_first_name',
        'con_last_name',
        'con_email',
        'con_job_title',
        'con_location',
        'con_distance',
        'con_public_identifier',
        'con_id',
        'con_tracking_id',
        'con_premium',
        'con_influencer',
        'con_jobseeker',
        'con_company_url',
        'con_member_urn'
    ];
}
