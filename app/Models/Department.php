<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }
}
