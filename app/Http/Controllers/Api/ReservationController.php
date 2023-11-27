<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Reservation;
use App\Models\CheckIn;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Member\CheckInResource;
use Illuminate\Http\Request;

class ReservationController extends BaseController
{
    public function getReservationsByMember($member_id = null) {
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', 1)->paginate(1);
        $data = [
            'reservations' => ReservationResource::collection($reservations),
            'pagination' => [
                'current_page' => $reservations->currentPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
                'prev_page_url' => $reservations->previousPageUrl(),
                'next_page_url' => $reservations->nextPageUrl(),
                'first_page_url' => $reservations->url(1),
                'last_page_url' => $reservations->url($reservations->lastPage()),
            ],
        ];
        return $this->sendResponse($data, 'Get member reservations successfully');
    }

    public function getCheckInsByReservation($reservation_id) {
        $checkIns = CheckIn::where('id', $reservation_id)->paginate(1);
        $data = [
            'checkIns' => CheckInResource::collection($checkIns),
            'pagination' => [
                'current_page' => $checkIns->currentPage(),
                'per_page' => $checkIns->perPage(),
                'total' => $checkIns->total(),
                'prev_page_url' => $checkIns->previousPageUrl(),
                'next_page_url' => $checkIns->nextPageUrl(),
                'first_page_url' => $checkIns->url(1),
                'last_page_url' => $checkIns->url($checkIns->lastPage()),
            ],
        ];
        return $this->sendResponse($data, 'Get checkins by reservation');
    }

    public function markAttendance(Request $request)
    {
        // Check if a check-in record already exists for the given reservation and today's date
        $existingCheckIn = CheckIn::where('reservation_id', $request->reservationId)
            ->whereDate('check_in_time', Carbon::today())
            ->first();

        if ($existingCheckIn) {
            // If a record already exists, you can return an appropriate response
            return $this->sendError('Check-in record already exists for this reservation today.');
        }

        // Create a new check-in record
        $checkIn = CheckIn::create([
            'reservation_id' => $request->reservationId,
            'check_in_time' => now()->format('Y-m-d H:i:s')
        ]);

        return $this->sendResponse(new CheckInResource($checkIn), 'Success');
    }

    public function markCheckOut(Request $request, $checkInId){
        $checkIn = CheckIn::findOrFail($checkInId);
        if($checkIn->check_out_time) {
            return $this->sendError('Check-out record already exists for this reservation today.');
        }
        $checkIn->check_out_time =  now()->format('Y-m-d H:i:s');
        $checkIn->save();
        return $this->sendResponse(new CheckInResource($checkIn), 'Success');
    }

    public function getReservationById($reservation_id) {
        $reservation = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', 1)->where('id', $reservation_id)->first();
        return $this->sendResponse(new ReservationResource($reservation), 'Get member reservations successfully');
    }
}
