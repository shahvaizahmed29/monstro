<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Achievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'name',
        'badge',
        'reward_points',
        'parent_id',
    ];

    public function program(){
        return $this->belongsTo(Program::class);
    }

    public function achievementRequirements(){
        return $this->belongsToMany(AchievementRequirements::class, 'achievement_requirements', 'achievement_id', 'action_id');
    }

    public function memberAchievements(){
        return $this->belongsToMany(MemberAchievement::class, 'member_achievements', 'achievement_id', 'member_id');
    }

}