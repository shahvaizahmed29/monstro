<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationRolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_role_id',
        'name',
        'create',
        'view',
        'update',
        'delete',
    ];

    /**
     * Get the role associated with this permission.
     */
    public function locationRole()
    {
        return $this->belongsTo(LocationRole::class);
    }
}
