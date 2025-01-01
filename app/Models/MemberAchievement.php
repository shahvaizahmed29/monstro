<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_id',
        'member_id',
        'status',
        'note',
        'progress',
        'date_achieved'
    ];

    protected $casts = [
        'progress' => 'integer',
        'date_achieved' => 'datetime',
    ];

    public function achievement(){
        return $this->belongsTo(Achievement::class);
    }

    public function member(){
        return $this->belongsTo(Member::class);
    }
}