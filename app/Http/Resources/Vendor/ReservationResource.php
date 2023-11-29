<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\CheckInResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Vendor\SessionResource;
use App\Http\Resources\Vendor\MemberResource;


class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reservation = [
            'id' => $this->id,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'status' => $this->status,
            'session' => $this->whenLoaded('session', function () {
                return new SessionResource($this->session);
            }),
            'checkIns' => $this->whenLoaded('checkIns', function () {
                return new MemberResource($this->checkIns);
            })
        ];

        return $reservation;
    }
}
