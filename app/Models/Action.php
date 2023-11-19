<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function achievementRequirements(){
        return $this->belongsToMany(AchievementRequirements::class, 'achievement_requirements', 'action_id', 'achievement_id');
    }

}
