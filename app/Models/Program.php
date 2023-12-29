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
        'avatar'
    ];

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function programLevels(){
        return $this->hasMany(ProgramLevel::class);
    }

    public function achievements(){
        return $this->hasMany(Achievement::class);
    }
    
    public function members(){
        return $this->belongsToMany(Location::class, 'member_programs', 'program_id', 'member_id');
    }

    public function monthlyRetentionRate()
    {
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        $newStudents = $this->programLevels()
            ->whereHas('sessions', function ($query) use ($currentMonth) {
                $query->where('created_at', '>=', $currentMonth);
            })
            ->count();

        $retainedStudents = $this->programLevels()
            ->whereHas('sessions', function ($query) use ($previousMonth) {
                $query->where('created_at', '>=', $previousMonth);
            })
            ->whereHas('sessions', function ($query) use ($currentMonth) {
                $query->where('created_at', '<', $currentMonth);
            })
            ->count();

        if ($newStudents > 0) {
            $retentionRate = ($retainedStudents / $newStudents) * 100;
        } else {
            $retentionRate = 0;
        }

        return $retentionRate;
    }

    public function totalActiveStudents()
    {
        return $this->programLevels()
            ->whereHas('sessions', function ($query) {
                $query->where('status', Session::ACTIVE);
            })
            ->count();
    }

    public function totalStudents()
    {
        return $this->members()->count();
    }
}