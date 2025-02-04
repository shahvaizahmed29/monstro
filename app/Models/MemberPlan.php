<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'vendor_id',
        'status',
        'family',
        'program_id',
        'family_member_limit',
        'contract_id'
    ];

    public function vendors()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function pricing(){
        return $this->hasOne(MemberPlanPricing::class);
    }

    public function contract(){
        return $this->belongsTo(Contract::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
      }
}
