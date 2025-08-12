<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\User;

class Activity extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'activity_type',
        'status',
        'level_score',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
