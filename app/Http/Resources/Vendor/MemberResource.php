<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'referralCode' => $this->referral_code,
            'avatar' => $this->avatar,
            'status' => ($this->status == 1) ? 'Active' : 'In Active',
            'currentProgram' => $this->currentProgram(),
            'currentProgramLevel' => $this->currentProgramLevel(),
            'activityStatus' => $this->isActive() ? 'Active' : 'Not Active',
            'reservations' => $this->whenLoaded('reservations', function () {
                return new ReservationResource($this->reservations);
            }),
            'goHighLevelContactId' => $this->when($this->go_high_level_contact_id !== null, $this->go_high_level_contact_id),
            'lastSeen' => $this->when($this->last_seen !== null, $this->last_seen)
        ];
        return $member;
    }
}
