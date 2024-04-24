<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class CustomReservationResource extends JsonResource
{
    public function toArray($request){
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $reservationsByDay = [];

        // Loop through each weekday
        foreach ($weekdays as $dayName) {
            $dayReservations = [];

            // Check if reservations exist for the current day
            if (isset($this[$dayName])) {
                // Loop through reservations for the current day
                foreach ($this[$dayName] as $reservation) {
                    $dayReservations[] = [
                        'programName' => $reservation->session->programLevel->program->name ?? null,
                        'programDescription' => $reservation->session->programLevel->program->description ?? null,
                        'locationName' => $reservation->session->programLevel->program->location->name ?? null,
                        'day' => $dayName,
                        'time' => $reservation->session->{$dayName},
                        'date' => $reservation->session->start_date
                    ];
                }
            }

            // Add reservations for the current day to the array
            $reservationsByDay[$dayName] = $dayReservations;
        }

        return $reservationsByDay;
    }

}

