<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model {
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'achievement_id',
        'location_id',
        'images',
        'icon',
        'required_points',
        'limit_per_member'
    ];

    protected $casts = [
        'images' => 'array', // Ensure images are stored as an array
    ];

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function rewardClaims()
    {
        return $this->hasMany(RewardClaim::class);
    }
}
