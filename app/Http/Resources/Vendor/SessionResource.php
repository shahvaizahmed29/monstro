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
        $timezone = $request->header('Timezone', 'UTC');
        $program = $this->program()->first();
        $programLevel = $this->programLevel()->first();

        $session = [
            'id' => $this->id,
            'programLevelId' => $programLevel->id,
            'programLevelName' => $programLevel->name,
            'programId' => $program->id,
            'programName' => $program->name,
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
            'currentStatus' => $this->getCurrentStatusAttribute($timezone),
            'programLevel' => $this->whenLoaded('programLevel', function () {
                return new ProgramLevelResource($this->programLevel);
            }),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
        ];


        return $session;
    }
}
