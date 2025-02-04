<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPlanPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'billing_period',
        'member_plan_id',
        'stripe_price_id'
    ];

    public function memberPlan()
    {
        return $this->belongsTo(MemberPlan::class);
    }

}
