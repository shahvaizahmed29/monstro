<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'related_member_id',
        'relationship',
        'is_payer',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function relatedMember()
    {
        return $this->belongsTo(Member::class, 'related_member_id');
    }
}
