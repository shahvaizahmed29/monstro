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
use App\Models\Session;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Vendor\MemberResource;

class MemberController extends BaseController
{
    public function getMembersByLocation($location_id){
        // $location = Location::find($location_id);
        // if($location->vendor_id != auth()->user()->vendor->id) {
        //     return $this->sendError('Vendor not authenticated', [], 403);
        // }
        $members = Member::whereHas('locations', function ($query) use ($location_id) {
            $query->where('locations.id', $location_id);
        })->whereHas("user.roles", function ($q){
            $q->where('name', \App\Models\User::MEMBER);
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

    public static function createMemberFromGHL($contact) {
        try {
            if(isset($contact['customFields'])) {
                $customFields = $contact['customFields'];
                foreach($customFields as $customField) {
                    $programLevel = ProgramLevel::with(['program'])->where('custom_field_ghl_value', $customField['value'])->first();
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
                                'end_data' => $session->end_date
                            ]);
                            DB::commit();
                        } catch(\Exception $error) {
                            DB::rollback();
                            $data = [
                                'contact' => $contact,
                                'customField' => $customField
                            ];
                            return $this->sendError('Something went wrong while creating contact!', json_encode($data));
                        }
                    }
                } 
            }
          
        } catch(\Exception $error) {
            return $this->sendError('Something went wrong while creating contact!', json_encode($contact));
        }
    }
}
