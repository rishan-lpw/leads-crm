<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;
    protected $table = 'lead';
    public $timestamps = true;
    protected $fillable = [
        'ad_id', 'cust_id', 'user_id', 'type', 'propty_type', 'service_type', 'street', 'city', 'heading', 'desc',
        'price', 'alt_price', 'alt_currency', 'price_type', 'price_monthly', 'price_land_pp', 'price_land_total',
        'pic', 'pic_count', 'youtube_link', 'video_link', 'image_360',
        'contact_type', 'contact_name', 'email',
        'lat', 'lng',
        'blocked', 'is_active', 'is_trending', 'is_pin', 'is_favorite', 'status', 'source', 'posted_date', 'weight', 'score',
        'ad_url'
    ];

    protected $casts = [
        'posted_date' => 'date',
        'is_active' => 'integer',
        'is_trending' => 'integer',
        'is_pin' => 'integer',
        'is_favorite' => 'integer',
        'blocked' => 'integer',
        'score' => 'float',
    ];

    // belongs to user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'cust_id');
    }

    // has many activities
    public function activities()
    {
        return $this->hasMany(Activity::class, 'lead_id');
    }
}
