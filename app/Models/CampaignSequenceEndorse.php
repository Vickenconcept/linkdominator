<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSequenceEndorse extends Model
{
    protected $table = 'campaign_sequence_endorse';
    
    protected $fillable = [
        'campaign_id',
        'node_model',
        'link_model'
    ];
}
