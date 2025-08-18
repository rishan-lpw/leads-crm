<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'customer_password_resets';

    protected $fillable = [
        'customer_id',
        'performed_by',
        'action',
    ];
}
