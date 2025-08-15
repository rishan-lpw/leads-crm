<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityFollowUp extends Model
{

    protected $table = 'activity_follow_up';

    protected $fillable = [
        'activity_id',
        'follow_up_time',
        'status',
        'level_score',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
