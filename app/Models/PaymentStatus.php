<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStatus extends Model
{
    protected $table = 'payment_status';

    protected $fillable = [
        'payment_status', 'color', 'is_enable', 'created_at', 'updated_at'
    ];

    public $timestamps = true;
}
