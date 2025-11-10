<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AddOn;
use App\Models\Role;
use App\Models\Activity;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id', 'firstname', 'surname', 'mobile', 'mobile_alt', 'email',
        'address', 'add_id', 'role_id', 'membership_exp_date', 'payment_exp_date', 'membership_status',
        'last_boost_added_date', 'phones'
    ];

    protected $casts = [
        'payment_exp_date' => 'date',
        'membership_exp_date' => 'date',
        'last_boost_added_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'phones' => 'array',
    ];

    protected $table = 'customer';
    
    // Allow setting custom ID values (from API uid)
    public $incrementing = false;

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'add_id');
    }

    public function addOns()
    {
        return $this->hasMany(AddOn::class, 'customer_id', 'id');
    }

    // Add accessor methods to ensure dates are properly handled
    public function getPaymentExpDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getMembershipExpDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getLastBoostAddedDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }
}
