<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationships
    public function postList():HasMany
    {
        // return posts | that have foreign key called id | which is equal to the current id
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
    public function commentList():HasMany
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }
    public function likeList():HasMany
    {
        return $this->hasMany(Like::class, 'user_id', 'id');
    }
    public function followersList():HasMany
    {
        return $this->hasMany(Follower::class, 'to_user_id', 'id');
    }
    public function followingList():HasMany
    {
        return $this->hasMany(Follower::class, 'from_user_id', 'id');
    }
}
