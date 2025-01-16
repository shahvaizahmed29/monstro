<?php

namespace App\Http\Resources\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member\ProgramLevelResource;
use Carbon\Carbon;

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
        $currentTime = Carbon::now()->tz($timezone);
        $currentDayOfWeek = strtolower($currentTime->format('l'));
        $duration = json_decode($this->duration_time, true);
        $session = [
            'id' => $this->id,
            'programLevelId' => $this->program_level_id,
            'programId' => $this->program_id,
            'durationTime' => isset($duration[$currentDayOfWeek]) ? $duration[$currentDayOfWeek] : 0 ,
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
