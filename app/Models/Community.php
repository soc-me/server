<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;
    protected $table = 'communities';
    protected $fillable = [
        'community_name',
        'community_description',
        'community_icon_image_url',
        'community_banner_image_url',
        'owner_user_id',
        'hide_posts_from_home'
    ];
}
