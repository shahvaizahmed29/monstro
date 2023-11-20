<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'referral_code',
        'avatar'
    ];

    public function locations(){
        return $this->belongsToMany(Location::class, 'member_locations', 'member_id', 'location_id');
    }

    public function achievements(){
        return $this->belongsToMany(MemberAchievement::class, 'member_achievements', 'member_id', 'achievement_id');
    }

    public function rewardClaims(){
        return $this->hasMany(MemberRewardClaim::class);
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }

    public function programs(){
        return $this->belongsToMany(Location::class, 'member_programs', 'member_id', 'program_id');
    }
    
}