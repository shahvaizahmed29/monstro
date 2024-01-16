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
use App\Models\Setting;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GHLController;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Vendor\MemberResource;
use App\Services\GHLService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MemberController extends BaseController
{
    
    protected $ghlService;

    public function __construct(GHLService $ghlService){
        $this->ghlService = $ghlService;
    }

    public function getMembersByLocation(){
        //Code commented out below becuase auth guard is not applied anymore.
        // $location = Location::find($location_id);
        // if($location->vendor_id != auth()->user()->vendor->id) {
        //     return $this->sendError('Vendor not authenticated', [], 403);
        // }
        $location = request()->location;
        $locationId = $location->id;
        $membersByLocation = Member::whereHas('locations', function ($query) use ($locationId) {
            $query->where('locations.id', $locationId);
        })->whereHas("user.roles", function ($q){
            $q->where('name', \App\Models\User::MEMBER);
        })->with([
            'reservations.checkIns' => function ($query) {
                $query->latest('check_in_time');
            }
        ])->get();

        $members = $membersByLocation->map(function ($membersByLocation) {
            $latestCheckInTime = $membersByLocation->reservations->pluck('checkIns.0.check_in_time')->first();
        
            if ($latestCheckInTime) {
                $carbonInstance = Carbon::parse($latestCheckInTime);
                $membersByLocation->last_seen = $carbonInstance->diffForHumans();
            } else {
                $membersByLocation->last_seen = null;
            }
        
            return $membersByLocation;
        });

        $members->each(function ($member) {
            $member->unsetRelation('reservations');
            $member->unsetRelation('checkIns');
        });
        
        return $this->sendResponse(MemberResource::collection($members), 'Members with details for the location.');
    }

    public function getMemberDetails($member_id){
        $location = request()->location;
        $locationId = $location->id;
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', $member_id)->get();
        if(count($reservations)) {
            $memberLocationId = $reservations[0]->session->programLevel->program->location_id;
            if($memberLocationId != $locationId) {
                return $this->sendError('Member doesnot exist');
            }
        }
        $member_details = Member::where('id', $member_id)->first();
        
        $go_high_level_contact_id = DB::table('member_locations')
            ->where('member_id', $member_id)
            ->where('go_high_level_location_id', $location->go_high_level_location_id)
            ->pluck('go_high_level_contact_id')
            ->first();

        $member_details['go_high_level_contact_id'] = $go_high_level_contact_id;

        $data = [
            'memberDetails' => new MemberResource($member_details),
            'reservations' => ReservationResource::collection($reservations)
        ];
        return $this->sendResponse($data, 'Member details with session reservations and program');
    }

    public static function createMemberFromGHL($contact, $location, $programLevelId) {
        try {
            DB::beginTransaction();
            $session = Session::where('program_level_id', $programLevelId)->where('status', Session::ACTIVE)->latest()->first();
            $user = User::where('email', $contact['email'])->first();
            if(!$user) {
                $user = User::create([
                    'name' => isset($contact['contactName']) ? $contact['contactName'] : '',
                    'email' => $contact['email'],
                    'password' => bcrypt($contact['email'].'@'.Carbon::now()->year.'!!'),
                    'email_verified_at' => now()
                ]);
                $user->assignRole(User::MEMBER);
                $randomNumberMT = mt_rand(100, 999);
                $member = Member::create([
                    'name' => (isset($contact['firstName']) ? $contact['firstName'] : ' ').' ' .(isset($contact['lastName']) ? $contact['lastName'] : ''),
                    'email' =>  $contact['email'],
                    'phone' => isset($contact['phone']) ? $contact['phone'] : '',
                    'referral_code' => $randomNumberMT.$user->id,
                    'user_id' => $user->id
                ]);
            } else {
                $member = $user->member;
            }

            $reservation = Reservation::updateOrCreate([
                'session_id' => $session->id,
                'member_id' =>  $member->id
            ],[
                'session_id' => $session->id,
                'member_id' =>  $member->id,
                'status' => Reservation::ACTIVE,
                'start_date' => Carbon::today()->format('Y-m-d'),
                'end_date' => $session->end_date
            ]);
            
            $member->locations()->sync([$location->id => [
                'go_high_level_location_id' => $location->go_high_level_location_id,
                'go_high_level_contact_id' => $contact['id']
            ]], false);

            DB::commit();
            return true;
        } catch(\Exception $error) {
            DB::rollback();
            Log::info('===== Create New Member =====');
            Log::info(json_encode($contact));
            Log::info(json_encode($programLevelId));
            Log::info($error->getMessage());
            return false;
        }
    }

    public static function generateLocationLevelKey($location_id) {
        $ghlIntegration = Setting::where('name', 'ghl_integration')->first();
        $tokenObj = Http::withHeaders([
            'Authorization' => 'Bearer '.$ghlIntegration['value'],
            'Version' => '2021-07-28'                
        ])->asForm()->post('https://services.leadconnectorhq.com/oauth/locationToken', [
            'companyId' => $ghlIntegration['meta_data']['companyId'],
            'locationId' => $location_id,
        ]);

        if ($tokenObj->failed()) {
            $tokenObj->throw();
        }
        
        $url = 'https://services.leadconnectorhq.com/contacts/?locationId='.$location_id.'&limit=100';

        return $tokenObj->json();
    }

    public function getContacts(){
        try{
            $location = request()->location;
            $name = request()->name;
            $locationId = $location->go_high_level_location_id;
            return $this->ghlService->getContactsByName($locationId,$name);
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
