<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberRewardClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'previous_points',
        'date_claimed',
        'member_id',
        'reward_id',
        'status'
    ];

    public function member(){
        return $this->belongsTo(Member::class);
    }

    public function reward(){
        return $this->belongsTo(Reward::class);
    }

}
