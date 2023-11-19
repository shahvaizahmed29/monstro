<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'program_id',
        'parent_id'
    ];

    public function program(){
        return $this->belongsTo(Program::class);
    }

    public function parent(){
        return $this->belongsTo(ProgramLevel::class, 'parent_id');
    }

    public function sessions(){
        return $this->hasMany(Session::class);
    }

}