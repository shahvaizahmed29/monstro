<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

class MemberReferrals extends Model
{
    use HasFactory, SoftDeletes;

    public const ACTIVE = 1; 
    public const INACTIVE = 2;

    protected $fillable = [
        'member_id',
        'location_id',
        'referred_member_id'
    ];

    public function location() {
      return $this->belongsTo(Location::class);
    }
}
