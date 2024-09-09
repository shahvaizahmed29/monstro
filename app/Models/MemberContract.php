<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'contract_id',
        'stripe_plan_id',
        'content',
        'signed',
    ];

    public function members()
    {
        return $this->belongsTo(Member::class);
    }

    public function stripePlan(){
        return $this->belongsTo(StripePlan::class);
    }

}
