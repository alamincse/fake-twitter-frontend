<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'tweet',
        'total_likes',
    ];

//    protected $casts = [
//        'created_at' => 'datetime:Y-m-d h:i:s a',
//    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(TweetLike::class, 'tweet_id');
    }
}
