<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'content',
        'user_id',
        'community_id',
    ];

    // Relationships
    public function commentList():HasMany
    {
        return $this->hasMany(Comment::class, 'post_id', 'id');
    }
    public function likeList():HasMany
    {
        return $this->hasMany(Like::class, 'post_id', 'id');
    }
    public function belongsToUser():BelongsTo
    {
        // return user | if this model's user_id | if user has an id that is equal to this model's user_id
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
