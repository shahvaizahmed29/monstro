<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorProgress extends Model
{
    use HasFactory;

    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }

    public function progressStep(){
        return $this->belongsTo(ProgressStep::class);
    }

}
