<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'staffs';

    protected $fillable = [
      'first_name',
      'last_name',
      'email',
      'phone',
      'avatar',
      'user_id',
      'role_id',
      'location_id'
    ];

    public function location(): BelongsTo
    {
      return $this->belongsTo(Location::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo {
      return $this->belongsTo(Role::class);
    }
}
