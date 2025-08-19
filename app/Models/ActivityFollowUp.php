<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityFollowUp extends Model
{

    use HasFactory;

    protected $table = 'activity_follow_up';

    protected $fillable = [
        'follow_up_time',
        'status',
        'level_score',
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
