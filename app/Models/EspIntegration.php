<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EspIntegration extends Model
{
    protected $fillable = [
        'user_id',
        'mailchimp',
        'getresponse',
        'emailoctopus',
        'converterkit',
        'mailerlite',
        'webhook'
    ];
}
