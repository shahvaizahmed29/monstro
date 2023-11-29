<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'company_email',
        'company_website',
        'company_address',
        'logo'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function locations(){
        return $this->hasMany(Location::class);
    }

}
