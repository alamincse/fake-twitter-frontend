<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\HasApiTokens;

class Participant extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'total_follower',
        'profile',
        'timeline',
        'status',
    ];

    public function scopeActive($query): void
    {
        $query->whereStatus(1);
    }

    public function tweets(): HasMany
    {
        return $this->hasMany(Tweet::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(TweetLike::class, 'participant_id');
    }

    public function followers()
    {
        return $this->hasMany(Follower::class, 'participant_id');
    }

    public function following()
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }
}
