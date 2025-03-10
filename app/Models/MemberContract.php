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
        'member_plan_id',
        'content',
        'signed',
        'location_id',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function memberPlan(){
        return $this->belongsTo(MemberPlan::class);
    }

    public function location() {
        return $this->belongsTo(Location::class);
    }

    public function contract() {
        return $this->belongsTo(Contract::class);
    }

}
