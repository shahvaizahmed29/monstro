<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'badge',
        'points',
        'program_id',
        'location_id'
    ];

    public function program(){
        return $this->belongsTo(Program::class);
    }

    public function actions(){
        return $this->belongsToMany(Action::class, 'achievement_actions', 'achievement_id', 'action_id')->withPivot('count');
    }    

    public function members(){
        return $this->belongsToMany(Member::class, 'member_achievements', 'achievement_id', 'member_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function memberAchievements()
    {
        return $this->hasMany(MemberAchievement::class);
    }

}