<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\User;
use App\Models\Lead;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activity';

    protected $fillable = [
        'lead_id',
        'activity_type',
        'notes',
        'scheduled_at',
        'due_at',
        'last_checked_at',
        'action',
        'qty',
        'value',
        'ad_id',
        'comments',
        'date_time',
        'assigned_by',
        'old_am',
        'activity_follow_up_id',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // An activity has one follow-up
    public function followUp()
    {
        return $this->hasOne(ActivityFollowUp::class, 'activity_id');
    }
}
