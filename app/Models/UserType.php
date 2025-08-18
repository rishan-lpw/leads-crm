<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    protected $table = 'user_type';

    protected $fillable = [
        'type_name',
        'sub_type',
        'activities',
    ];
}
