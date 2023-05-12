<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification_Curr extends Model
{
    use HasFactory;
    protected $table = 'notifications_curr';
    protected $fillable = [
        'notification_of_user_id',  
        'notification_from_user_id',
        'on_post_id',
        'is_read',
        'type',
        'message'
    ];

}
