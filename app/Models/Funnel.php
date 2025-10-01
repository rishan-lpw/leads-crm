<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funnel extends Model
{
    protected $table = 'funnel';

    protected $fillable = [
        'category', 'stage', 'description', 'created_at', 'updated_at'
    ];

    public $timestamps = true;
}
