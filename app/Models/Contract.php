<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'content',
        'title',
        'isDraft',
        'editable',
        'location_id'
    ];

    public function vendors()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function memberPlans(){
        return $this->hasMany(MemberPlan::class);
    }

}
