<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Vendor\ProgramLevelResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $session = [
            'id' => $this->id,
            'programLevelId' => $this->program_level_id,
            'durationTime' => $this->duration_time,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'monday' => $this->monday,
            'tuesday' => $this->tuesday,
            'wednesday' => $this->wednesday,
            'thursday' => $this->thursday,
            'friday' => $this->friday,
            'saturday' => $this->saturday,
            'sunday' => $this->sunday,
            'status' => $this->status,
            'currentStatus' => $this->getCurrentStatusAttribute(),
            'programLevel' => $this->whenLoaded('programLevel', function () {
                return new ProgramLevelResource($this->programLevel);
            }),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
        ];


        return $session;
    }
}
