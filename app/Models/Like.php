<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'post_id'
    ];

    //Relationships
    public function belongsToUser():BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function belongsToPost():BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
}
