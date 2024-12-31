<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use HasFactory;

    public const NO_OF_CLASSES_ATTENDED = "No Of Classes Attended";
    public const NO_OF_LEVELS_COMPLETED = "No Of Levels Completed";
    public const No_OF_REFERRALS_ADDED = "No Of Referrals Added";
    public const SPEND_AMOUNT_AT_A_LOCATION = "Spend Amount At a Location";

    protected $fillable = [
        'name'
    ];

    public function achievements(){
        return $this->belongsToMany(Achievement::class, 'achievement_actions', 'action_id', 'achievement_id');
    }    

}
