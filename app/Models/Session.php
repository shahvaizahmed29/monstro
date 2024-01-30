<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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

    public function program(){
        return $this->belongsTo(Program::class);
    }

    public function programLevel(){
        return $this->belongsTo(ProgramLevel::class);
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }

    public function getCurrentStatusAttribute($timezone)
    {

        // Convert start_date and end_date to the desired timezone
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        $now = Carbon::now($timezone);

        if ($now->lt($startDate)) {
            return "Session Not Started";
        } elseif ($now->gt($endDate)) {
            return "Session Ended";
        } else {
            $today = strtolower($now->format('l'));
            $nextClassStart = Carbon::parse($this->{$today}, $timezone)->setTimezone('UTC');
            $nextClassStart->setDate($now->year, $now->month, $now->day);

            if ($now->greaterThanOrEqualTo($nextClassStart) && $now->lessThanOrEqualTo($nextClassStart->copy()->addMinutes($this->duration_time))) {
                return "In Progress";
            } else {
                $nextClassStart->addDay();
                $timeRemaining = $now->diff($nextClassStart);

                return "In " . $timeRemaining->format('%H:%I') . " hrs";
            }
        }
    }
}