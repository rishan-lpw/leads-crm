<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMerge extends Model
{
    protected $table = 'customer_merge';

    protected $fillable = [
        'primary_customer_id',
        'secondary_customer_id',
        'merge_at',
        'merged_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'primary_customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'merged_by');
    }
}
