<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'lead';

    protected $fillable = [
        'user_type_id',
        'name',
        'posted_date',
        'source',
        'am',
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
    
}
