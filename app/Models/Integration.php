<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'service',
        'api_key',
        'secret_key',
        'access_token',
        'refresh_token',
        'integration_id',
        'additional_settings',
        'location_id'
    ];

    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }
}