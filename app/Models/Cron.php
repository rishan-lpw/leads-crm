<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cron extends Model
{
    protected $table = 'cron';

    protected $fillable = [
        // Update for newly added fields
        'name',
        'category',
        'member',
        'rule_1_days',
        'rule_2_days',
    ];

    protected $casts = [
        'member' => 'array', // This will automatically convert between array and JSON
    ];

    public function logs()
    {
        return $this->hasMany(CronLog::class);
    }
}
