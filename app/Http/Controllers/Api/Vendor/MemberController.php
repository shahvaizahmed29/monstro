<?php

namespace App\Http\Controllers\Api\Vendor;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Session;
use App\Models\Setting;
use App\Models\Program;
use App\Models\CheckIn;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\CreateMemberResource;
use App\Http\Resources\Member\ProgramResource;
use App\Http\Resources\Vendor\ReservationResource;
use App\Http\Resources\Vendor\MemberResource;
use App\Http\Resources\Vendor\SessionResource;
use App\Mail\InviteMembers;
use App\Mail\MemberRegistration;
use App\Models\ProgramLevel;
use App\Notifications\NewMemberNotification;
use App\Services\GHLService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

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
        })->paginate(10);

        $data = [
            'members' => MemberResource::collection($membersByLocation),
            'pagination' => [
                'current_page' => $membersByLocation->currentPage(),
                'per_page' => $membersByLocation->perPage(),
                'total' => $membersByLocation->total(),
                'prev_page_url' => $membersByLocation->previousPageUrl(),
                'next_page_url' => $membersByLocation->nextPageUrl(),
                'first_page_url' => $membersByLocation->url(1),
                'last_page_url' => $membersByLocation->url($membersByLocation->lastPage()),
            ],
        ];
        
        return $this->sendResponse($data, 'Members with details for the location.');
    }

    public function profileUpdate(Request $request, $member_id){
        try{
            $member = Member::find($member_id);

            if(!$member){
                return $this->sendError('Member does not exist.', [], 400);
            }
            
            $member->name = $request->name;
            $member->email = $request->email;
            $member->phone = $request->phone;
            $member->save();
            
            return $this->sendResponse(new MemberResource($member), 'Members updated successfully.');            
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getMemberDetails($member_id){
        try{
            $location = request()->location;
            $reservations = Reservation::with(['checkIns', 'session', 'session.programLevel','session.programLevel.program'])
            ->whereHas('session.programLevel', function ($query) {
                return $query->whereNull('deleted_at');
            })->whereHas('session.program', function ($query) {
                return $query->whereNull('deleted_at');
            })
            ->where('member_id', $member_id)->get();
            
            $member_details = Member::with(['rewards', 'achievements', 'children'])->where('id', $member_id)->first();

            if(count($reservations)) {
                $reservationIds = $reservations->pluck('id')->toArray();
                $latestCheckInTime = CheckIn::whereIn('reservation_id', $reservationIds)->latest()->first();
                if ($latestCheckInTime) {
                    $carbonInstance = Carbon::parse($latestCheckInTime->check_in_time);
                    $member_details['last_seen'] = $carbonInstance->diffForHumans();
                } else {
                    $member_details['last_seen'] = null;
                }
            } else {
                $member_details['last_seen'] = null;
            }

            $member_details['current_level'] = $this->getCurrentLevel($member_id);

            $data = array_merge(
                (new MemberResource($member_details))->resolve(),
                [
                    'reservations' => ReservationResource::collection($reservations)->resolve(),
                ]
            );
            
            return $this->sendResponse($data, 'Member details with session reservations and program');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getCurrentLevel($member_id){
        try{
            $currentLevel = null;
            $member = Member::findOrFail($member_id);
            $member->load(['reservations' => function ($query) {
                $query->where('status', Reservation::ACTIVE);
            }]);
            
            if(count($member->reservations) > 0){
                $currentLevel = $member->reservations[0]->session->programLevel->name;
            }else{
                $currentLevel = "----";
            }
            
            return $currentLevel;
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function createMember(CreateMemberResource $request){
        try{
            $location = request()->location;
            $password = Str::random(8);

            $user = User::where('email', $request->email)->first();

            if(!$user){
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($password),
                    'email_verified_at' => now()
                ]);
    
                $user->assignRole(User::MEMBER);
                $randomNumberMT = mt_rand(100, 999);
    
                $member = Member::create([
                    'name' => $request->name,
                    'email' =>  $request->email,
                    'phone' => $request->phone,
                    'referral_code' => $randomNumberMT.$user->id,
                    'user_id' => $user->id
                ]);

                if(isset($request->programLevelId)){
                    $session = Session::where('program_level_id', $request->programLevelId)->where('status', Session::ACTIVE)->latest()->first();

                    if($session){
                        Reservation::updateOrCreate([
                            'session_id' => $session->id,
                            'member_id' =>  $member->id
                        ],[
                            'session_id' => $session->id,
                            'member_id' =>  $member->id,
                            'status' => Reservation::ACTIVE,
                            'start_date' => Carbon::today()->format('Y-m-d'),
                            'end_date' => $session->end_date
                        ]);
                    }
                }
    
                $member->locations()->sync([$location->id]);
    
                $data =  [];
                $data['name'] = $request->name;
                $data['email'] = $request->email;
                $data['password'] = $password;
                // Notification::route('mail', $user->email)->notify(new NewMemberNotification($data));
    
                return $this->sendResponse(new MemberResource($member), 'Member created successfully.');
            }else{
                return $this->sendError('Member already registered with this email', [], 400);
            }
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function memberStatusUpdate($member_id){
        try{ 
            $member = Member::find($member_id);

            if(!$member){
                return $this->sendError('Member does not exist.', [], 400);
            }

            $member->status = \App\Models\Member::INACTIVE;
            $member->save();

            return $this->sendResponse(new MemberResource($member), 'Member status updated to inactive successfully.');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public static function createMemberFromGHL($contact, $location, $programLevelId, $program) {
        try {
            DB::beginTransaction();
            $session = Session::where('program_level_id', $programLevelId)->where('status', Session::ACTIVE)->latest()->first();

            $password = "M2".Str::random(8)."#$!".mt_rand(100, 999);
            $user = User::where('email', $contact['email'])->first();
            if(!$user) {
                $user = User::create([
                    'name' => (isset($contact['firstName']) ? $contact['firstName'] : ($contact['name'] ? $contact['name'] : ' ')).' ' .(isset($contact['lastName']) ? $contact['lastName'] : ''),
                    'email' => $contact['email'],
                    'password' => bcrypt($password),
                    'email_verified_at' => now()
                ]);
                $user->assignRole(User::MEMBER);
                $randomNumberMT = mt_rand(100, 999);
                $member = Member::create([
                    'first_name' => (isset($contact['firstName']) ? $contact['firstName'] : $contact['name']),
                    'last_name' => (isset($contact['lastName']) ? $contact['lastName'] : ''),
                    'email' =>  $contact['email'],
                    'phone' => isset($contact['phone']) ? $contact['phone'] : '',
                    'referral_code' => $randomNumberMT.$user->id,
                    'user_id' => $user->id
                ]);
            } else {
                $member = $user->member;
            }

            $programLevel = ProgramLevel::with(['program'])->where('id', $programLevelId)->first();

            $alreadyEnrolledInProgramLevel = $member->reservations()->whereHas('session.programLevel', function ($query) use ($programLevelId) {
                $query->where('id', '!=', $programLevelId);
            })->whereHas('session', function ($query) use ($programLevel) {
                $query->where('program_id', '=', $programLevel->program->id);
            })->count();

            if($alreadyEnrolledInProgramLevel > 0){
                return false;
            }else{
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

                $member->locations()->sync([$location->id]);

                DB::commit();
                Mail::to($contact['email'])->send(new MemberRegistration($contact['firstName'], $contact['lastName'], $program->name, $contact['email'], $password, $randomNumberMT.$user->id));
                return true;
            }
           
        } catch(\Exception $error) {
            DB::rollback();
            Log::info('===== Create New Member =====');
            Log::info(json_encode($contact));
            Log::info(json_encode($programLevelId));
            Log::info($error->getMessage());
            return false;
        }
    }

    public static function createMemberFromRegistration($contact, $location, $programLevelId) {
        try {
            DB::beginTransaction();
            $session = Session::where('program_level_id', $programLevelId)->where('status', Session::ACTIVE)->latest()->first();
            $user = User::where('email', $contact['email'])->first();
            if(!$user) {
                $user = User::create([
                    'name' => $contact['firstName'].' '.$contact['lastName'],
                    'email' => $contact['email'],
                    // 'password' => bcrypt($contact['email'].'@'.Carbon::now()->year.'!!'),
                    'password' => bcrypt($contact['password']),
                    'email_verified_at' => now()
                ]);
                $user->assignRole(User::MEMBER);
                $randomNumberMT = mt_rand(100, 999);
                $member = Member::create([
                    'first_name' => $contact['firstName'],
                    'last_name' => $contact['lastName'],
                    'email' =>  $contact['email'],
                    'phone' => isset($contact['phone']) ? $contact['phone'] : '',
                    'referral_code' => $randomNumberMT.$user->id,
                    'user_id' => $user->id
                ]);
            } else {
                $member = $user->member;
            }

            $programLevel = ProgramLevel::with(['program'])->where('id', $programLevelId)->first();

            $alreadyEnrolledInProgramLevel = $member->reservations()->whereHas('session.programLevel', function ($query) use ($programLevelId) {
                $query->where('id', '!=', $programLevelId);
            })->whereHas('session', function ($query) use ($programLevel) {
                $query->where('program_id', '=', $programLevel->program->id);
            })->count();

            if($alreadyEnrolledInProgramLevel > 0){
                return false;
            }else{
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

                $member->locations()->sync([$location->id]);

                DB::commit();
                return true;
            }
           
        } catch(\Exception $error) {
            DB::rollback();
            Log::info('===== Create New Member =====');
            Log::info(json_encode($contact));
            Log::info(json_encode($programLevelId));
            Log::info($error->getMessage());
            return false;
        }
    }

    public function getContacts(){
        try{
            $location = request()->location;
            $name = request()->name;
            $locationId = $location->go_high_level_location_id;
            
            $ghl_contacts = $this->ghlService->getContactsByName($locationId, $name);

            $members = $this->getMembers($location->id);
            $members = json_decode($members, true);

            foreach ($members as &$member) {
                $member['contactName'] = $member['name'];
                unset($member['name']);
            }

            $contacts = array_merge_recursive($ghl_contacts, ['contacts' => $members]);

            $uniqueContacts = [];
            foreach ($contacts['contacts'] as $contact) {
                $email = $contact['email'];
                if (!isset($uniqueContacts[$email])) {
                    $uniqueContacts[$email] = $contact;
                }
            }

            $uniqueContacts = array_values($uniqueContacts);
            $contacts['contacts'] = $uniqueContacts;

            return $contacts;
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getMembers($location_id){
        $members = Member::whereHas('locations', function ($query) use ($location_id) {
            $query->where('id', $location_id);
        })->get();

        return $members;
    }

    public function getMembersByProgram($id) {
        $location = request()->location;
        $locationId = $location->id;
        $program = Program::where('id', $id)->where('location_id', $locationId)->first();
        if ($program) {
            
            $activeSessions = Session::with(['reservations' => function($q) {
                $q->where('status', Reservation::ACTIVE);
            }, 'reservations.member','programLevel','program'])->whereHas('reservations', function($q) {
                $q->where('status', Reservation::ACTIVE);
            })->where('program_id', $program->id)->get();

            return $this->sendResponse(SessionResource::collection($activeSessions), 'program active members.');
        } else {
            return $this->sendError('Program not found.', 404);
        }
        
    }

    public function addMemberManually(Request $request){
        try {
            $location = request()->location;
            $program = Program::with(['programLevels', 'location'])->where('id', $request->programId)->first();
            if(!$location){
                return $this->sendError('Location Not Found');
            }
            if(!$program){
                return $this->sendError('Program Not Found');
            }
            $contact = $request->all();
            $addMember = MemberController::createMemberFromGHL($contact, $location, $program->programLevels[0]->id, $program);
            if($addMember == true){
                return $this->sendResponse('Success', 'Member synced successfully');
            }else{
                return $this->sendError('Member is already enrolled in another program level', [], 400);
            }
        } catch(Exception $error) {
            return $this->sendError('Something went wrong!', $error->getMessage());
        }
    }

    public function getMemberPrograms($member_id){
        try{
            $reservations = Reservation::where('member_id', $member_id)
                ->with('session.programLevel.program')
                ->get();

            $programs = [];

            foreach ($reservations as $reservation) {
                $session = $reservation->session;
                $programLevel = $session->programLevel;
                
                if($programLevel){
                    $program = $programLevel->program;
                    
                    if($program){
                        if (!isset($programs[$program->id])) {
                            $programs[$program->id] = $program;
                        }
                    }
                }

            }

            return $this->sendResponse(ProgramResource::collection($programs), 'Get programs related to specific member');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getProgramsForMemberNotEnrolled($member_id) {
        try{
            $location = request()->location;
            $reservations = Reservation::where('member_id', $member_id)
                ->with('session.programLevel.program')
                ->get();
            $programs = [];
            foreach ($reservations as $reservation) {
                $session = $reservation->session;
                $programLevel = $session->programLevel;
                
                if($programLevel){
                    $program = $programLevel->program;
                    
                    if($program){
                        $programs[] = $program->id;
                    }
                }
            }
            $programsObj = Program::with(['programLevels'])->whereNotIn('id', $programs)->where('location_id', $location->id)->get();
            return $this->sendResponse(ProgramResource::collection($programsObj), 'Get programs related to specific member');
        } catch(Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function memberPasswordReset($member_id){
        try{
            $member = Member::findOrFail($member_id);

            if(!$member){
                return $this->sendError("Member not found", [], 400);
            }

            $password = bcrypt("Monstro@".Carbon::now()->year);
            $member->user->password = $password;
            $member->user->save();

            return $this->sendResponse("Success", 'Member password updated successfully.');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getFamilyMembers($member_id) {
        try{
            $member = Member::with('children')->findOrFail($member_id);

            if(!$member){
                return $this->sendError("Member not found", [], 400);
            }

            return $this->sendResponse($member, 'Family Members List.');        
        } catch(Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function inviteMember(Request $request) {
        Mail::to($request->email)->send(new InviteMembers("https://localhost:3000/registration/{$request->programId}/4/signup"));
        return response()->json(['message' => $request->email]);
    }
}
