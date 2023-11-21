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
        'meta_data',
    ];

    public function vendors(){
        return $this->belongsToMany(Location::class, 'vendor_locations', 'location_id', 'vendor_id');
    }

    public function members(){
        return $this->belongsToMany(Location::class, 'member_locations', 'location_id', 'member_id');
    }

    public function programs(){
        return $this->hasMany(Program::class);
    }

}
