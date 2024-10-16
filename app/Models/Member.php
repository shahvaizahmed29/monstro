<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    public const ACTIVE = 1; 
    public const INACTIVE = 2;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'referral_code',
        'avatar',
        'current_points',
        'parent_id'
    ];

    public function locations(){
        return $this->belongsToMany(Location::class, 'member_locations', 'member_id', 'location_id');
    }    

    public function achievements(){
        return $this->belongsToMany(Achievement::class, 'member_achievements', 'member_id', 'achievement_id');
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }

    public function parent(){
        return $this->belongsTo(Member::class, 'parent_id');
    }

    public function children(){
        return $this->hasMany(Member::class, 'parent_id');
    }

    public function programs(){
        return $this->belongsToMany(Program::class, 'member_programs', 'member_id', 'program_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool{
        $activeReservationsCount = $this->reservations()
            ->where('status', \App\Models\Reservation::ACTIVE)
            ->count();

        return $activeReservationsCount > 0 ? true : false;
    }

    public function reedemPoints(){
        $redeemPoints = 0;
        $rewards = $this->rewards()->get();
        foreach($rewards as $reward) {
            if($reward->reward->type == Reward::POINTS){
                $redeemPoints = $redeemPoints + $reward->reward->reward_points;
            }
        }
        return $redeemPoints;
    }

    public function rewards(){
        return $this->hasMany(MemberRewardClaim::class);
    }

}
