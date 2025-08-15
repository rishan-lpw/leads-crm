<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Customer;

class AddOn extends Model
{

    use HasFactory;

    protected $table = 'add_on';

    protected $fillable = [
        'title',
        'description',
        'price',
        'location',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
