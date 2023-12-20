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

    public function latestCheckInTimes($memberId)
    {
        $member = $this->with([
            'reservations.checkIns' => function ($query) {
                $query->latest('check_in_time')->limit(1);
            }
        ])->find($memberId);

        return $member->reservations->pluck('checkIns.0.check_in_time');
    }

}