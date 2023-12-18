<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Models\CheckIn;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Member\CheckInResource;
use App\Http\Resources\Vendor\MeetingResource;
use App\Http\Resources\Vendor\MemberResource;
use App\Http\Resources\Vendor\ReservationResource;
use App\Models\Member;
use Exception;

class ReservationController extends BaseController
{
    public function getReservationsByMember($member_id) {
        $member = Member::find($member_id);
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', $member_id)->paginate(25);
        if(count($reservations) > 0) {
            $location = $reservations[0]->session->programLevel->program->location;
            //Code commented out below becuase auth guard is not applied anymore.
            // if($location->vendor_id != auth()->user()->vendor->id) {
            //     return $this->sendError('Vendor not authorize, Please contact admin.', [], 403);
            // }
        }
        $data = [
            'reservations' => ReservationResource::collection($reservations),
            'memberDetails' => new MemberResource($member),
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
        return $this->sendResponse($data, 'Reservations by member.');
    }

    public function markAttendance(Request $request)
    {
        $reservation = Reservation::find($request->reservationId);
        $location = $reservation->session->programLevel->program->location;
        //Code commented out below becuase auth guard is not applied anymore.
        // if($location->vendor_id != auth()->user()->vendor->id) {
        //     return $this->sendError('Vendor not authorize, Please contact admin', [], 403);
        // }

        // Check if a check-in record already exists for the given reservation and today's date
        $existingCheckIn = CheckIn::where('reservation_id', $reservation->id)
            ->whereDate('check_in_time', Carbon::today())
            ->first();

        if ($existingCheckIn) {
            // If a record already exists, you can return an appropriate response
            return $this->sendError('Attendence for today already recorded for this program.');
        }

        // Create a new check-in record
        $checkIn = CheckIn::create([
            'reservation_id' => $reservation->id,
            'check_in_time' => now()->format('Y-m-d H:i:s')
        ]);

        return $this->sendResponse(new CheckInResource($checkIn), 'Attendance marked.');
    }

    public function getCheckInsByReservation($reservation_id) {
        $reservation = Reservation::find($reservation_id);
        //Code commented out below becuase auth guard is not applied anymore.
        // if($reservation->member_id != auth()->user()->member->id) {
        //     return $this->sendError('Member not authorize, Please contact support', [], 403);
        // }
        $checkIns = CheckIn::where('id', $reservation_id)->latest()->get();
        return $this->sendResponse($checkIns, 'Checkins by reservation.');
    }

    public function memberUpcomingMeetings($memberId){
        try {
            //Get the member reservations along with sessions, program level and program
            $reservations = Reservation::where('member_id', $memberId)
                ->with(['session.programLevel.program'])
                ->whereHas('session', function ($query){
                    $query->whereDate('start_date', '>=', Carbon::today());
                })
                ->get();

            $meetings = $reservations->map(function ($reservation) {
                $session = $reservation->session;
                $program = $session->programLevel->program;
                
                // Calculating start and end time of meeting
                $startTime = Carbon::parse($session->start_date)->addHours($session->time);
                $endTime = $startTime->copy()->addHours($session->duration_time);
    
                return [
                    'title' => $program->name,
                    'start' => $startTime->format('Y-m-d\TH:i:s'),
                    'end' => $endTime->format('Y-m-d\TH:i:s'),
                ];
            })->toArray();
    
            return $this->sendResponse(MeetingResource::collection($meetings), 'Member Upcoming Meetings.');
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
