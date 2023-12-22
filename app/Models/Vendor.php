<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'go_high_level_location_id',
        'plan_id',
        'user_id',
        'company_name',
        'company_email',
        'company_website',
        'company_address',
        'logo',
        'pin',
        'phone_number'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function locations(){
        return $this->hasMany(Location::class);
    }

    // public function plan()
    // {
    //     return $this->belongsTo(Plan::class);
    // }

    // public function steps()
    // {
    //     return $this->hasMany(Step::class);
    // }

}
