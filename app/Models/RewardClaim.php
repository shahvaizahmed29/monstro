<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RewardClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['reward_id', 'member_id', 'previous_points', 'date_claimed', 'status'];

    protected $casts = [
        'previous_points' => 'double',
    ];

    public function member(){
        return $this->belongsTo(Member::class);
    }

    public function reward(){
        return $this->belongsTo(Reward::class);
    }

}
