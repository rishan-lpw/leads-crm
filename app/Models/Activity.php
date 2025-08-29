<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\User;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activity';

    protected $fillable = [
        'customer_id',
        'user_id',
        'activity_type',
        'notes',
        'scheduled_at',
        'due_at',
        'last_checked_at',
        'auto_status_updated',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function activityFollowUp()
    {
        return $this->belongsTo(ActivityFollowUp::class, 'activity_follow_up_id');
    }
}
