<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    protected $table = 'cron_log';

    protected $fillable = [
        'cron_id',
        'started_at',
        'finished_at',
        'status',
        'output',
    ];

    public function cron()
    {
        return $this->belongsTo(Cron::class);
    }
}
