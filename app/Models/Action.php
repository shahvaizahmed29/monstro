<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function achievements(){
        return $this->belongsToMany(Achievement::class, 'achievement_actions', 'action_id', 'achievement_id');
    }    

}
