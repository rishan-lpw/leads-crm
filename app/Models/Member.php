<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    // Define the relationship with the AddOn model
    public function addOns()
    {
        return $this->hasMany(AddOn::class);
    }
}
