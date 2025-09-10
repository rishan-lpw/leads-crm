<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ad_media extends Model
{
    // id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    // ad_id BIGINT UNSIGNED,
    // has_pic TINYINT(1) DEFAULT 0,
    // pic_count INT DEFAULT 0,
    // pics_link TEXT,
    // youtube_link VARCHAR(255),
    // video_link VARCHAR(255),
    // image_360_link VARCHAR(255),
    // ad_id (Foreign key)
    protected $table = 'ad_media';

    protected $fillable = [
        'ad_id',
        'has_pic',
        'pic_count',
        'pics_link',
        'youtube_link',
        'video_link',
        'image_360_link',
    ];
}
