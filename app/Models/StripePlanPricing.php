<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StripePlanPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'billing_period',
        'stripe_plan_id',
        'stripe_price_id'
    ];

    public function stripePlan()
    {
        return $this->belongsTo(StripePlan::class);
    }

}
