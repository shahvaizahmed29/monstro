<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Member\CheckInResource;
use App\Http\Resources\Vendor\MemberResource;
use App\Http\Resources\Vendor\ReservationResource;
use App\Models\AchievementActions;
use App\Models\Action;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\MemberAchievement;
use App\Models\Reservation;
use App\Models\Session;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationController extends BaseController
{
    public function getReservationsByMember($member_id)
    {   
        try {
            $member = Member::find($member_id);
            // Fetch the nearest reservation, trusting the resource to handle "in progress" logic
            $reservation = Reservation::with(['session.programLevel', 'session.program'])
            ->whereHas('session', function ($query) {
                $query->whereNull('deleted_at')->where('status', Session::ACTIVE);
            })
            ->where('member_id', $member_id)
            ->where('status', Reservation::ACTIVE)
            ->orderBy('start_date')
            ->first();
            // if(count($reservations) > 0) {
            // $location = $reservations[0]->session->programLevel->program->location;
            //Code commented out below becuase auth guard is not applied anymore.
            // if($location->vendor_id != auth()->user()->vendor->id) {
            //     return $this->sendError('Vendor not authorize, Please contact admin.', [], 403);
            // }
            // }
            $data = [
                'reservation' => new ReservationResource($reservation),
                'memberDetails' => new MemberResource($member)
            ];
            return $this->sendResponse($data, 'Reservations by member.');
        } catch (Exception $e) {
            Log::info('===== ReservationController - markAttendance() - error =====');
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function markAttendance(Request $request)
    {
        try {
            DB::beginTransaction();

            $reservation = Reservation::with(['session'])->where('id', $request->reservationId)->first();

            $currentTime = Carbon::now();

            $currentDayOfWeek = strtolower($currentTime->format('l'));

            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $currentTime->format('Y-m-d') . ' ' . $reservation->session->{$currentDayOfWeek});

            //Code commented out below becuase auth guard is not applied anymore.
            // $location = $reservation->session->programLevel->program->location;
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
                'check_in_time' => now()->format('Y-m-d H:i:s'),
                'time_to_check_in' => $startTime,
            ]);

            // Finding a achivement action related to defined no of classes attendented
            $action = Action::where('name', Action::NO_OF_CLASSES)->first();

            $achievement_action = AchievementActions::with(['achievement'])->whereHas('achievement', function ($q) use ($reservation) {
                $q->where('program_id', $reservation->session->program->id);
            })->where('action_id', $action->id)->first();

            if ($achievement_action) {
                // Checking criteria count
                $eligibilityCriteria = $achievement_action->count;
                $attendanceCount = $reservation->checkIns->count();

                // Checking for eligibility
                if ($attendanceCount >= $eligibilityCriteria) {
                    $existingMemberAchievement = MemberAchievement::where('achievement_id', $achievement_action->achievement->id)
                        ->where('member_id', $reservation->member_id)->first();

                    // Check for if member achievment is already exist
                    if (!$existingMemberAchievement) {
                        //Creating a new achievment for member
                        $member_achievement = MemberAchievement::create([
                            'achievement_id' => $achievement_action->achievement->id,
                            'member_id' => $reservation->member_id,
                            'status' => 1,
                            'note' => 'Achievement accomplished on number of classes completion',
                            'date_achieved' => now(),
                        ]);

                        // Fidning member in order to get the current member achieved points
                        $member = Member::find($reservation->member_id);
                        $currentPoints = $member->current_points;

                        if ($member_achievement) {
                            // Updating mmeber overall points
                            $currentPoints = $currentPoints + $achievement_action->achievement->reward_points;
                        }

                        $member->current_points = $currentPoints;
                        $member->save();
                    }
                }
            }

            DB::commit();
            return $this->sendResponse(new CheckInResource($checkIn), 'Attendance marked.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ReservationController - markAttendance() - error =====');
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getCheckInsByReservation($reservation_id)
    {
        $reservation = Reservation::find($reservation_id);
        //Code commented out below becuase auth guard is not applied anymore.
        // if($reservation->member_id != auth()->user()->member->id) {
        //     return $this->sendError('Member not authorize, Please contact support', [], 403);
        // }
        $checkIns = CheckIn::where('reservation_id', $reservation_id)->latest()->get();
        return $this->sendResponse($checkIns, 'Checkins by reservation.');
    }

}
