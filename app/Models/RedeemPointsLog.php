<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedeemPointsLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'previous_points',
        'redeem_points',
        'current_points',
        'date_claimed',
        'member_id'
    ];

    public function member(){
        return $this->belongsTo(Member::class);
    }
}
