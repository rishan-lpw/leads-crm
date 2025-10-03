<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserValue extends Model
{
    protected $table = 'user_value';

    protected $fillable = [
        'category',
        'value',
    ];
}
