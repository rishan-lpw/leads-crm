<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad_verts extends Model
{
    // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    // ad_id VARCHAR(50) UNIQUE,
    // uid VARCHAR(50),
    // type VARCHAR(50),
    // property_type VARCHAR(50),
    // service_type VARCHAR(50),
    // heading VARCHAR(255),
    // description TEXT,
    // submit_date TIMESTAMP NULL,
    // posted_date TIMESTAMP NULL,
    // price DECIMAL(15,2) DEFAULT 0,
    // alt_price DECIMAL(15,2) DEFAULT 0,
    // alt_currency VARCHAR(10),
    // price_type VARCHAR(50),
    // price_monthly DECIMAL(15,2) DEFAULT 0,
    // price_sqft DECIMAL(15,2) DEFAULT 0,
    // city VARCHAR(100),
    // street VARCHAR(255),
    // avail VARCHAR(100),
    // source VARCHAR(100),
    // status VARCHAR(50) DEFAULT 'active',
    // created_at TIMESTAMP NULL,
    // updated_at TIMESTAMP NULL

    protected $table = 'ad_verts';

    protected $fillable = [
        'ad_id',
        'uid',
        'type',
        'property_type',
        'service_type',
        'heading',
        'description',
        'submit_date',
        'posted_date',
        'price',
        'alt_price',
        'alt_currency',
        'price_type',
        'price_monthly',
        'price_sqft',
        'city',
        'street',
        'avail',
        'source',
        'status',
    ];

    protected $casts = [
        'submit_date' => 'datetime',
        'posted_date' => 'datetime',
        'price' => 'decimal:2',
        'alt_price' => 'decimal:2',
        'price_monthly' => 'decimal:2',
        'price_sqft' => 'decimal:2',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
