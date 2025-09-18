<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activity';

    protected $fillable = [
        'lead_id', 'user_id', 'action', 'qty', 'value', 'ad_id', 'comments', 'reminder', 'date_time', 'old_am'
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'reminder' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function scopeForUser($query, $user)
{
    if ($user->user_level_id == 1) {
        // Only show activities for leads assigned to this user
        return $query->whereHas('lead', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }
    
        return $query;
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
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    
}
