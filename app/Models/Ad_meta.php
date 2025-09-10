<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ad_meta extends Model
{
    // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    // ad_id BIGINT UNSIGNED,
    // contact_type VARCHAR(50),
    // contact_name VARCHAR(100),
    // tel VARCHAR(50),
    // email VARCHAR(150),
    // lat DECIMAL(20,15),
    // lng DECIMAL(20,15),
    // zoom INT,
    // last_ip VARCHAR(50),
    // poster_ip VARCHAR(50),
    // hits INT,
    // was_active TINYINT(1) DEFAULT 0,
    // is_active TINYINT(1) DEFAULT 1,
    // is_verified TINYINT(1) DEFAULT 0,
    // is_blocked TINYINT(1) DEFAULT 0,
    // block_reason VARCHAR(255),
    // is_trending TINYINT(1) DEFAULT 0,
    // trending_date TIMESTAMP NULL,
    // boosted_count INT DEFAULT 0,
    // hot_deals TINYINT(1) DEFAULT 0,
    // house_post_url VARCHAR(255),
    // FOREIGN KEY (ad_id)

    protected $table = 'ad_meta';

    protected $fillable = [
        'ad_id',
        'contact_type',
        'contact_name',
        'tel',
        'email',
        'lat',
        'lng',
        'zoom',
        'last_ip',
        'poster_ip',
        'hits',
        'was_active',
        'is_active',
        'is_verified',
        'is_blocked',
        'block_reason',
        'is_trending',
        'trending_date',
        'boosted_count',
        'hot_deals',
        'house_post_url',
    ];

    protected $casts = [
        'lat' => 'decimal:15',
        'lng' => 'decimal:15',
        'trending_date' => 'datetime',
        'was_active' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_blocked' => 'boolean',
        'is_trending' => 'boolean',
        'hot_deals' => 'boolean',
    ];
}
