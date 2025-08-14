<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class AddOn extends Model
{
    protected $fillable = [
        'customer_id',
        'title',
        'description',
        'price',
        'location',
        'type',
        'method_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
