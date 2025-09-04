<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'lead';

    protected $fillable = [
        'user_id',
        'customer_id',
        'posted_date',
        'source',
        // 'am',
        'status',
        'latest_comments',
        'last_update_date',
        'last_update_by',
        'tel',
        'price',
    ];

    protected $casts = [
        'posted_date' => 'date',
        'last_update_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

}
