<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberPayment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'payer_id',
        'beneficiary_id',
        'program_id',
        'member_plan_id'
    ];
}
