<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignList extends Model
{
    protected $fillable = [
        'campaign_id',
        'list_hash',
        'list_source'
    ];

    public function campaign()
    {
        return $this->belongs(\App\Models\Campaign::class, 'campaign_id');
    }
}
