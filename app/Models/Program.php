<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_id',
        // 'custom_field_ghl_id',
        'name',
        'description',
        'capacity',
        'min_age',
        'max_age',
        'avatar',
        'status'
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function levels(){
        return $this->hasMany(ProgramLevel::class);
    }

    public function achievements(){
        return $this->hasMany(Achievement::class);
    }
    
    public function members(){
        return $this->belongsToMany(Location::class, 'member_programs', 'program_id', 'member_id');
    }

}