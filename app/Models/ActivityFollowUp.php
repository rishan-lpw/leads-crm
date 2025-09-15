<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFollowUp extends Model
{

    use HasFactory;

    protected $table = 'activity_follow_up';

    // id
    // activity_id            
    // follow_up_time  
    // status      
    // level_score
    // reminder_at   
    // completed_at  
    // auto_status_updated 
    // created_at
    // updated_at

    protected $fillable = [
        'activity_id',
        'follow_up_time',
        'status',
        'level_score',
        'reminder_at',
        'completed_at',
        'auto_status_updated',
        'created_at',
        'updated_at',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
}
