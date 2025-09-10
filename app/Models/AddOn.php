<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Customer;

class AddOn extends Model
{

    use HasFactory;

    // Fields: id,	title,	desc_short,	description,	order_desc,	price,	discount_price,	valid_period,	image,	offer,	site,	active_method.	
    protected $table = 'add_ons';

    protected $fillable = [
        'title',
        'desc_short',
        'description',
        'order_desc',
        'price',
        'discount_price',
        'valid_period',
        'image',
        'offer',
        'site',
        'active_method',
        'category_id',
        'customer_id',
    ];

    public function category() 
    {
        return $this->belongsTo(Category::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
