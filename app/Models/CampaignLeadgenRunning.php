<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignLeadgenRunning extends Model
{
    protected $table = 'campaign_leadgen_running';
    
    protected $fillable = [
        'lead_id',
        'lead_src',
        'lead_list',
        'campaign_id',
        'current_node_key',
        'next_node_key',
        'accept_status',
        'status_last_id',
    ];

    public function campaign()
    {
        return $this->belongs(\App\Models\Campaign::class, 'campaign_id');
    }
}
