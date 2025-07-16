<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'status',
        'sequence_type',
        'process_condition',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function campaignList()
    {
        return $this->campaign_list;
    }
}
