<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'go_high_level_location_id',
        'name',
        'address',
        'city',
        'state',
        'logo_url',
        'country',
        'postal_code',
        'website',
        'email',
        'phone',
        'vendor_id',
        'meta_data',
        'timezone',
        'industry'
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }

    public function members(){
        return $this->belongsToMany(Member::class, 'member_locations', 'location_id', 'member_id');
    }

    public function staff(){
        return $this->hasOne(Staff::class);
    }

    public function programs(){
        return $this->hasMany(Program::class);
    }

    public function supportTicket(){
        return $this->hasOne(SupportTicket::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
      }

}
