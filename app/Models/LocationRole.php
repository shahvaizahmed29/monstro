<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationRole extends Model
{
    use HasFactory;

    protected $table = 'location_roles';

    protected $fillable = [
        'location_id',
        'name',
        'color',
    ];

    /**
     * Get the location associated with the role.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the permissions associated with the role.
     */
    public function permissions()
    {
        return $this->hasMany(LocationRolePermission::class);
    }
}
