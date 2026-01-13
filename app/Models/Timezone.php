<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timezone extends Model
{
    protected $fillable = [
        'id',
        'country_code',
        'country_name',
        'time_zone',
        'gmt_offset',
        'timezone_link',
    ];

    public $incrementing = false;
    protected $keyType = 'int';
}
