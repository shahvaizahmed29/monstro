<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberRewardClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'points_claimed',
        'current_points',
        'date_claimed',
    ];

    public function member(){
        return $this->belongsTo(Member::class);
    }

}