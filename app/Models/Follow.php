<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory;
    protected $fillable = [
        'from_user_id',
        'to_user_id'
    ];

    //Relationships
    public function fromUser():BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }
    public function toUser():BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id', 'id');
    }
}
