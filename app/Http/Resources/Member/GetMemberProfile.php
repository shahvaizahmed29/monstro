<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetMemberProfile extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $member = [
            'id' => $this->id,
            'name' => $this->member->first_name.' '.$this->member->last_name,
            'firstName' => $this->member->first_name,
            'lastName' => $this->member->last_name,
            'email' => $this->member->email,
            'phone' => $this->member->phone,
            'referralCode' => $this->member->referral_code,
            'avatar' => $this->member->avatar,
            'memberId' => $this->member->id,
        ];
        return $member;
    }
}
