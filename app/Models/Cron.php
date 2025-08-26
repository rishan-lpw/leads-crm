<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cron extends Model
{
    protected $table = 'cron';

    protected $fillable = [
        'name',
        'command',
        'frequency',
        'is_active',
        'last_run_at',
        'category',
        'rules',
        'visibility',
        'source_highlight',
        'allow_manual_trigger',
    ];

    public function logs()
    {
        return $this->hasMany(CronLog::class);
    }
}
