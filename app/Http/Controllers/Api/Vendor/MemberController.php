<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\User;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\ProgramLevel;
use App\Models\Location;
use App\Models\MemberLocation;
use App\Models\Session;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Vendor\MemberResource;

class MemberController extends BaseController
{
    public function getMembersByLocation($location_id){
        $location = Location::find($location_id);
        if($location->vendor_id != auth()->user()->vendor->id) {
            return $this->sendError('Vendor not authenticated', [], 403);
        }
        $members = Member::whereHas('locations', function ($query) use ($location_id) {
            $query->where('locations.id', $location_id);
        })->get();
        return $this->sendResponse(MemberResource::collection($members), 'Location members');
    }

    public function getMemberDetails($member_id){
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', $member_id)->get();
        $member_details = Member::where('id', $member_id)->first();
        $data = [
            'memberDetails' => new MemberResource($member_details),
            'reservations' => ReservationResource::collection($reservations)
        ];
        return $this->sendResponse($data, 'Member details with session reservations and program');
    }

    public static function createMemberFromGHL($contact, $locationId) {
        try {
            if(isset($contact['customFields'])) {
                $customFields = $contact['customFields'];
                foreach($customFields as $customField) {
                    \Log::info(json_encode($customField['value']));
                    if (strpos($customField['value'], '_') === false) {
                        continue;
                    }
                    $parts = explode('_', $customField['value']);
                    \Log::info(json_encode($parts));
                    if(count($parts)<2) {
                        continue;
                    }

                    $programLevelId = $parts[1];
                    $programLevel = ProgramLevel::with(['program'])->where('id', $programLevelId)->first();
                    \Log::info(json_encode($programLevel));
                    
                    if($programLevel) {
                        try {
                            DB::beginTransaction();
                            $session = Session::where('program_level_id',$programLevel->id)->latest()->first();
                            $user = User::create([
                                'name' => isset($contact['name']) ? $contact['name'] : '',
                                'email' => $contact['email'],
                                'password' => bcrypt($contact['email'].'@'.'2023!!'),
                                'email_verified_at' => now()
                            ]);
                            $user->assignRole(User::MEMBER);
                            $randomNumberMT = mt_rand(100, 999);
                            $member = Member::create([
                                'name' => isset($contact['name']) ? $contact['name'] : '',
                                'email' =>  $contact['email'],
                                'phone' => isset($contact['phone']) ? $contact['phone'] : '',
                                'referral_code' => $randomNumberMT.$user->id,
                                'user_id' => $user->id
                            ]);
                            $reservation = Reservation::create([
                                'session_id' => $session->id,
                                'member_id' =>  $member->id,
                                'status' => 1,
                                'start_date' => Carbon::today()->format('Y-m-d'),
                                'end_date' => $session->end_date
                            ]);
                            $location= Location::where('go_high_level_location_id', $locationId)->first();
                            // DB::table('member_locations')->insert([
                            //     'member_id' => $member->id,
                            //     'location_id' => $location->id,
                            //     'go_high_level_location_id' => $locationId
                            // ]);
                            \Log::info('========= 3 ========');
                            DB::commit();
                        } catch(\Exception $error) {
                            DB::rollback();
                            $data = [
                                'contact' => $contact,
                                'customField' => $customField
                            ];
                            throw $error;
                        }
                    }
                } 
            }
          
        } catch(\Exception $error) {
            throw $error;
        }
    }
}
