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
        //     $table->id();
        //     $table->bigInteger('ad_id')->unsigned();
        //     $table->bigInteger('cust_id')->unsigned();
        //     $table->bigInteger('user_id')->unsigned()->nullable();

        //     // Core fields
        //     $table->string('type', 50)->nullable();
        //     $table->string('propty_type', 100)->nullable();
        //     $table->string('service_type', 100)->nullable();
        //     $table->string('street', 255)->nullable();
        //     $table->string('city', 100)->nullable();
        //     $table->string('heading', 255)->nullable();
        //     $table->text('desc')->nullable();

        //     // Pricing
        //     $table->bigInteger('price')->nullable();
        //     $table->bigInteger('alt_price')->nullable();
        //     $table->string('alt_currency', 5)->nullable();
        //     $table->string('price_type', 50)->nullable();
        //     $table->bigInteger('price_monthly')->nullable();
        //     $table->bigInteger('price_land_pp')->nullable();
        //     $table->bigInteger('price_land_total')->nullable();

        //     // Media
        //     $table->boolean('pic')->default(false);
        //     $table->integer('pic_count')->nullable();
        //     $table->string('youtube_link', 255)->nullable();
        //     $table->string('video_link', 255)->nullable();
        //     $table->string('image_360', 255)->nullable();

        //     // Contact
        //     $table->string('contact_type', 50)->nullable();
        //     $table->string('contact_name', 255)->nullable();
        //     $table->string('email', 255)->nullable();

        //     // Location
        //     $table->decimal('lat', 12, 8)->nullable();
        //     $table->decimal('lng', 12, 8)->nullable();

        //     // Flags (use tinyint instead of varchar!)
        //     $table->tinyInteger('blocked')->default(0);
        //     $table->tinyInteger('is_active')->default(0);
        //     $table->tinyInteger('is_trending')->default(0);
            
        //     $table->timestamps();

        'ad_id', 'cust_id', 'user_id', 'type', 'propty_type', 'service_type', 'street', 'city', 'heading', 'desc',
        'price', 'alt_price', 'alt_currency', 'price_type', 'price_monthly', 'price_land_pp', 'price_land_total',
        'pic', 'pic_count', 'youtube_link', 'video_link', 'image_360',
        'contact_type', 'contact_name', 'email',
        'lat', 'lng',
        'blocked', 'is_active', 'is_trending', 'status', 'source'
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
