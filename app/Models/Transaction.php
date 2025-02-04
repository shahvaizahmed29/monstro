<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'description',
        'statement_description',
        'payment_method',
        'transaction_type',
        'amount',
        'status',
        'model',
        'program_id',
        'member_plan_id',
        'plan_id',
        'location_id',
        'member_id',
        'vendor_id',
        'staff_id',
        'payment_type'
    ];

    /**
     * Relationships
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function memberPlan()
    {
        return $this->belongsTo(MemberPlan::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
