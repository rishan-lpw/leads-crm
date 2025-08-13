<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Activity;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'address',
        'add_id',
        'role_id',
    ];

    protected $table = 'customer';

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
}
