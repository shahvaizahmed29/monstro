<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    public const ACTIVE = 1; 
    public const INACTIVE = 2; 
    public const COMPLETED = 3;    

    protected $fillable = [
        'program_level_id',
        'program_id',
        'duration_time',
        'start_date',
        'end_date',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'status'
    ];

    public function programLevel(){
        return $this->belongsTo(ProgramLevel::class);
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }

    public function getCurrentStatusAttribute()
    {
        $now = \Carbon\Carbon::now();
        $startDate = \Carbon\Carbon::parse($this->start_date);
        $endDate = \Carbon\Carbon::parse($this->end_date);

        // Check if the class is currently in progress
        if ($now->greaterThanOrEqualTo($startDate) && $now->lessThanOrEqualTo($endDate)) {
            // Calculate the time remaining for the next class
            $today = strtolower($now->format('l')); // Get the current day in lowercase (e.g., 'monday')         
            $nextClassStart = \Carbon\Carbon::parse($this->{$today});
            $nextClassStart->setDate($now->year, $now->month, $now->day); // Set the date to today
            // If the next class has already started today, move to the next day
            if ($now->greaterThanOrEqualTo($nextClassStart) && $now->lessThanOrEqualTo($nextClassStart->copy()->addMinutes($this->duration_time))) {
                return "In Progress";
            } else {
                $nextClassStart->addDay();
                // Calculate the time remaining for the next class
                $timeRemaining = $now->diff($nextClassStart);
    
                return "In ".$timeRemaining->format('%H:%I') . " hrs";
            }
        } else {
            return "Session Ended";
        }
    }
}