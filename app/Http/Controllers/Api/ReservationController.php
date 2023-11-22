<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Reservation;
use App\Models\CheckIn;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Member\CheckInResource;

class ReservationController extends BaseController
{
    public function getReservationsByMember($member_id = null) {
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', 1)->paginate(1);
        return $this->sendResponse(ReservationResource::collection($reservations), 'Success');
    }

    public function getCheckInsByReservation($reservation_id) {
        $checkIns = CheckIn::where('id', $reservation_id)->paginate(1);
        return $this->sendResponse(CheckInResource::collection($checkIns), 'Success');
    }
}
