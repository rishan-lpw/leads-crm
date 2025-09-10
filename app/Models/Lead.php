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
        'activity_id',
        'posted_date',
        'source',
        // 'am',
        'status',
        'latest_comments',
        'last_update_date',
        'last_update_by',
        'tel',
        'price',
        'Company_Name',
        'Source_Type',
        'Invoice_Name',
        'Invoice_Address',
        'LinkIn_Profile',
        'Member_Image',
        'Auto_Boost',
        'Auto_Boost_for_New_Ads',
        'Remarks',
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

    // has many activities
    public function activities()
    {
        return $this->hasMany(Activity::class, 'lead_id');
    }
}
