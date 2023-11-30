<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'member_id',
        'status',
        'start_date',
        'end_date',
    ];

    public function member(){
        return $this->belongsTo(Member::class);
    }

    public function checkIns(){
        return $this->hasMany(CheckIn::class);
    }

    public function session(){
        return $this->belongsTo(Session::class);
    }

    public function getCurrentStatusForTodayAttribute()
    {
        $now = \Carbon\Carbon::now();
        return $this->checkIns()->whereDate('check_in_time', $now)->count();
    }
}