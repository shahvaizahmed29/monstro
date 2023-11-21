<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Reservation;
use App\Http\Resources\Member\ReservationResource;

class ReservationController extends BaseController
{
    public function getReservationsByMember($member_id = null) {
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', 1)->paginate(25);
        return $this->sendResponse(ReservationResource::collection($reservations), 'Success');
    }
}
