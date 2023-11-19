<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberAchievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'achievement_id',
        'member_id',
        'status',
        'note',
        'date_achieved',
    ];

    public function achievement(){
        return $this->belongsTo(Achievement::class);
    }

    public function member(){
        return $this->belongsTo(Member::class);
    }

}