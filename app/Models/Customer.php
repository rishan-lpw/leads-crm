<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Role;

class Customer extends Model
{
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
}
