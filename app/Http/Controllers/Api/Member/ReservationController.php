<?php

namespace App\Http\Controllers\Api\Member;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\CheckIn;
use App\Http\Controllers\BaseController;
use App\Http\Resources\CustomReservationResource;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Member\CheckInResource;
use App\Http\Resources\Vendor\MeetingResource;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationController extends BaseController
{
    public function getReservationsByMember() {
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', auth()->user()->member->id)->paginate(25);
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
        $reservation = Reservation::find($reservation_id);
        if($reservation->member_id != auth()->user()->member->id) {
            return $this->sendError('Member not authorize, Please contact support', [], 403);
        }
        $checkIns = CheckIn::where('reservation_id', $reservation_id)->paginate(25);
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
        return $this->sendResponse($data, 'Checkins by reservation.');
    }

    public function markAttendance(Request $request){
        try{
            DB::beginTransaction();
            $reservation = Reservation::find($request->reservationId);
            if($reservation->member_id != auth()->user()->member->id) {
                return $this->sendError('Member not authorize', [], 403);
            }

            $currentTime = Carbon::now();
            $currentDayOfWeek = strtolower($currentTime->format('l'));
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $currentTime->format('Y-m-d') .' '.$reservation->session->{$currentDayOfWeek});

            // Check if a check-in record already exists for the given reservation and today's date
            $existingCheckIn = CheckIn::where('reservation_id', $request->reservationId)
                ->whereDate('check_in_time', Carbon::today())
                ->first();

            if ($existingCheckIn) {
                // If a record already exists, you can return an appropriate response
                return $this->sendError('Attendence for today already recorded for this program.');
            }

            // Create a new check-in record
            $checkIn = CheckIn::create([
                'reservation_id' => $request->reservationId,
                'check_in_time' => now()->format('Y-m-d H:i:s'),
                'time_to_check_in' => $startTime
            ]);

            DB::commit();
            return $this->sendResponse(new CheckInResource($checkIn), 'Success');
        }catch(Exception $e){
            DB::rollBack();
            Log::info('===== ReservationController - markAttendance() - error =====');
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getReservationById($reservation_id) {
        $reservation = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', auth()->user()->member->id)->where('id', $reservation_id)->first();
        return $this->sendResponse(new ReservationResource($reservation), 'Reservations.');
    }

}
