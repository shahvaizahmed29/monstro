<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Api\Vendor\MemberController as VendorMemberController;
use App\Http\Controllers\BaseController;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Resources\Member\AchievementRewardResource;
use App\Http\Resources\Member\ClaimedRewardResource;
use App\Http\Resources\Member\AchievementResource;
use App\Http\Resources\Member\GetMemberProfile;
use App\Http\Resources\Member\MemberResource;
use App\Http\Resources\Member\ProgramResource;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Member\LocationResource;
use App\Http\Resources\Member\MemberAchievementResource;
use App\Http\Resources\Member\RewardResource;
use App\Mail\RewardsClaimed;
use App\Http\Resources\Vendor\VendorResource;
use App\Models\Achievement;
use App\Models\Member;
use App\Models\MemberAchievement;
use App\Models\MemberRewardClaim;
use App\Models\Program;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Reward;
use App\Models\Contract;
use App\Models\Location;
use App\Models\MemberContract;
use App\Models\StripePlan;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MemberController extends BaseController
{
    public function profileUpdate(Request $request, $userId){
        try{
            $user = User::find($userId);

            if (!$user) {
                return $this->sendError('User not exist.', [], 400);
            }

            $member = $user->member;

            if (!$member) {
                return $this->sendError('Member not exist.', [], 400);
            }

            if ($request->has('name')) {
                $member->name = $request->name;
            }

            $member->save();
            return $this->sendResponse('Success', 'User updated successfully.');
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getLoginMember(){
        try{
            $member = auth()->user()->member;
            return $this->sendResponse(new MemberResource($member) , 'Get member successfully');
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getMemberEnrolledPrograms($vendorId){
        try{
            $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])
            ->whereHas('session.programLevel', function ($query) {
                return $query->whereNull('deleted_at');
            })->whereHas('session.program', function ($query) {
                return $query->whereNull('deleted_at');
            })->whereHas('session.programLevel.program.location', function ($query) use ($vendorId){
                $query->where('vendor_id', $vendorId);
            })
                ->where('member_id', auth()->user()->member->id)
                ->where('status', Reservation::ACTIVE)
                ->paginate(25);
            
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

            return $this->sendResponse($data, 'Member enrolled programs fetched successfully');
        }catch(Exception $error){
            return $error->getMessage();
        }
    }

    public function getMemberActiveVendors(){
        try{
            $reservations = Reservation::with(['session.programLevel.program.location.vendor'])
                ->where('member_id', auth()->user()->member->id)
                ->where('status', Reservation::ACTIVE)
                ->paginate(25);

                $vendors = $reservations->pluck('session.programLevel.program.location.vendor')->unique('id');    

                $data = [
                    'vendors' => VendorResource::collection($vendors),
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
    
                return $this->sendResponse($data, 'Member enrolled programs fetched successfully');

        }catch(Exception $error){
            return $error->getMessage();
        }
    }


    public function getMemberActiveLocations(){
        try{
            $locations = auth()->user()->member->locations;
                $data = [
                    'locations' => LocationResource::collection($locations),
                    // 'pagination' => [
                    //     'current_page' => $reservations->currentPage(),
                    //     'per_page' => $reservations->perPage(),
                    //     'total' => $reservations->total(),
                    //     'prev_page_url' => $reservations->previousPageUrl(),
                    //     'next_page_url' => $reservations->nextPageUrl(),
                    //     'first_page_url' => $reservations->url(1),
                    //     'last_page_url' => $reservations->url($reservations->lastPage()),
                    // ],
                ];

                return $this->sendResponse($data, 'Member locations fetched successfully');
        }catch(Exception $error){
            return $error->getMessage();
        }
    }

    public function getMemberRewards(){
        try{

            $locationId = request()->locationId;
            $rewards = MemberRewardClaim::with('reward')->whereHas('reward', function ($query) use ($locationId){
                return $query->where('location_id', $locationId);
            })->where('member_id', auth()->user()->member->id)->paginate(25);
            
            $data = [
                'rewards' => ClaimedRewardResource::collection($rewards),
                'pagination' => [
                    'current_page' => $rewards->currentPage(),
                    'per_page' => $rewards->perPage(),
                    'total' => $rewards->total(),
                    'prev_page_url' => $rewards->previousPageUrl(),
                    'next_page_url' => $rewards->nextPageUrl(),
                    'first_page_url' => $rewards->url(1),
                    'last_page_url' => $rewards->url($rewards->lastPage()),
                ],
            ];

            return $this->sendResponse($data, 'Rewards fetched successfully');
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getTradableRewards(){
        $member = Auth::user()->member;
        $rewards = Reward::where('type', Reward::POINTS)->where('location_id', request()->locationId)->with(["achievement"])->paginate(25);
        $data = [
            'rewards' => RewardResource::collection($rewards),
            'pagination' => [
                'current_page' => $rewards->currentPage(),
                'per_page' => $rewards->perPage(),
                'total' => $rewards->total(),
                'prev_page_url' => $rewards->previousPageUrl(),
                'next_page_url' => $rewards->nextPageUrl(),
                'first_page_url' => $rewards->url(1),
                'last_page_url' => $rewards->url($rewards->lastPage()),
            ],
        ];
        return $this->sendResponse($data, 'Reward List');
    }

    public function getAchievementRewards(){
        $member = Auth::user()->member;
        $locationId = request()->locationId;
        $alreadyAchieved = MemberAchievement::whereHas('achievement', function ($query) {
            return $query->whereNull('deleted_at');
        })->where('member_id', $member->id)->pluck('achievement_id');
        if(count($alreadyAchieved)) {
            $rewards = Reward::where('type', Reward::ACHIEVEMENT)->where('location_id', $locationId)->whereIn('achievement_id', $alreadyAchieved)->with(["achievement"])->paginate(25);
        } else {
            $rewards = Reward::where('type', Reward::ACHIEVEMENT)->where('location_id', $locationId)->with(["achievement"])->paginate(25);
        }
        $data = [
            'rewards' => RewardResource::collection($rewards),
            'pagination' => [
                'current_page' => $rewards->currentPage(),
                'per_page' => $rewards->perPage(),
                'total' => $rewards->total(),
                'prev_page_url' => $rewards->previousPageUrl(),
                'next_page_url' => $rewards->nextPageUrl(),
                'first_page_url' => $rewards->url(1),
                'last_page_url' => $rewards->url($rewards->lastPage()),
            ],
        ];
        return $this->sendResponse($data, 'Reward List');
    }

    public function getUnclaimedAchievements(){
        $member = Auth::user()->member;
        $alreadyAchieved = MemberAchievement::whereHas('achievement', function ($query) {
            return $query->whereNull('deleted_at');
        })->where('member_id', $member->id)->pluck('achievement_id');
        $programIds = Program::where('location_id', request()->locationId)->pluck('id');
        if(count($alreadyAchieved)){
            $achievements = Achievement::whereIn('program_id', $programIds)->whereNotIn('id', $alreadyAchieved)->paginate(25);
        } else {
            $achievements = Achievement::whereIn('program_id', $programIds)->paginate(25);
        }
        
        $data = [
            'achievements' => AchievementResource::collection($achievements),
            'pagination' => [
                'current_page' => $achievements->currentPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
                'prev_page_url' => $achievements->previousPageUrl(),
                'next_page_url' => $achievements->nextPageUrl(),
                'first_page_url' => $achievements->url(1),
                'last_page_url' => $achievements->url($achievements->lastPage()),
            ],
        ];
        return $this->sendResponse($data, 'Achievements fetched successfully');
    }

    public function getClaimedAchievements(){
        $member = Auth::user()->member;
        $programIds = Program::where('location_id', request()->locationId)->pluck('id');
        $achievements = MemberAchievement::whereHas('achievement', function ($query) use ($programIds){
            return $query->whereNull('deleted_at')->whereIn('program_id', $programIds);
        })->where('member_id', $member->id)->with("achievement")->paginate(25);

        $data = [
            'achievements' => MemberAchievementResource::collection($achievements),
            'pagination' => [
                'current_page' => $achievements->currentPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
                'prev_page_url' => $achievements->previousPageUrl(),
                'next_page_url' => $achievements->nextPageUrl(),
                'first_page_url' => $achievements->url(1),
                'last_page_url' => $achievements->url($achievements->lastPage()),
            ],
        ];
        return $this->sendResponse($data, 'Achievements List');
    }

    public function getMemberAchievements(){
        try{
            $achievements = Achievement::whereHas('members', function ($query) {
                $query->where('member_id', auth()->user()->member->id);
            })->paginate(25);

            $data = [
                'rewards' => AchievementResource::collection($achievements),
                'pagination' => [
                    'current_page' => $achievements->currentPage(),
                    'per_page' => $achievements->perPage(),
                    'total' => $achievements->total(),
                    'prev_page_url' => $achievements->previousPageUrl(),
                    'next_page_url' => $achievements->nextPageUrl(),
                    'first_page_url' => $achievements->url(1),
                    'last_page_url' => $achievements->url($achievements->lastPage()),
                ],
            ];

            return $this->sendResponse($data, 'Achievements fetched successfully');
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getCurrentPoints(){
        try{
            $member = Member::find(auth()->user()->member->id);
            $currentPoints = $member->current_points;

            $data = [
                'currentPoints' => $currentPoints
            ];

            return $this->sendResponse($data, 'Current points fetched successfully');            
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function updatePassword(PasswordUpdateRequest $request, $id){
        try {
            $user = User::find($id);

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->sendError('Incorrect old password.', [], 400);
            }

            $new_password = $request->input('newPassword');
            $user->password = bcrypt($new_password);
            $user->save();

            return $this->sendResponse('Success', 'Password set successfully');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getProfile(){
        try {
            $user = User::find(request()->user()->id);

            if (!$user) {
                return $this->sendError('User not found.', [], 404);
            }

            return $this->sendResponse(new GetMemberProfile($user), 200);
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function redeemPoints(Request $request){
        try {
            $member = Auth::user()->member;

            $reward = Reward::where("id", $request->rewardId)->first();
            
            if($reward->reward_points < $member->current_points){
                MemberRewardClaim::create([
                    'previous_points' => $member->current_points,
                    'date_claimed' => now(),
                    'member_id' => $member->id,
                    'reward_id' => $reward->id,
                    "status" => "Active"
                ]);
                $member->current_points = $member->current_points - $reward->reward_points;
                $member->save();
                $mailObject = new \stdClass();
                $mailObject->member = $member;
                $mailObject->reward = $reward;
                $mailObject->admin = false;
                Mail::to($member->email)->send(new RewardsClaimed($mailObject));
                $mailObject->admin = true;
                //replace admin email;
                Mail::to($member->email)->send(new RewardsClaimed($mailObject));
                return $this->sendResponse(new MemberResource($member) , 'Reward redeemed successfully');
            } else {
                return $this->sendError('You don\'t have enough points to claim this reward.', [], 400);
            }

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function claimRewardTradeable(Request $request){
        try {
            $member = Auth::user()->member;
            $reward = Reward::where("id", $request->rewardId)->where('type', Reward::POINTS)->first();
            $claimedCount = MemberRewardClaim::where("reward_id", $reward->id)->where("member_id", $member->id)->count();
            if($reward->limit_per_member <= $claimedCount){
                return $this->sendError('You have already exceeded the limit for this achievement, You cannot claim it at the moment.', [], 400);
            }
            $previousPoints = $member->current_points;
            $member->current_points = $member->current_points - $reward->reward_points;
            $member->save();
            MemberRewardClaim::create([
                'previous_points' => $previousPoints,
                'date_claimed' => now(),
                'member_id' => $member->id,
                'reward_id' => $reward->id,
                "status" =>  1
            ]);
            $mailObject = new \stdClass();
            $mailObject->member = $member;
            $mailObject->reward = $reward;
            $mailObject->admin = false;
            Mail::to($member->email)->send(new RewardsClaimed($mailObject));
            $mailObject->admin = true;
            //replace admin email;
            Mail::to($member->email)->send(new RewardsClaimed($mailObject));
            return $this->sendResponse(new MemberResource($member) , 'Reward redeemed successfully');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function claimRewardAchieveable(Request $request){
        try {
            $member = Auth::user()->member;
            $reward = Reward::where("id", $request->rewardId)->where('type', Reward::ACHIEVEMENT)->first();
            $memberAchievement = MemberAchievement::with('achievement')->where("id", $reward->achievement_id)->first();
            if($memberAchievement){
                return $this->sendError('You haven\'t achieved this goal to claim this reward.', [], 400);
            }
            $claimedCount = MemberRewardClaim::where("reward_id", $reward->id)->where("member_id", $member->id)->count();
            if($claimedCount >= $reward->limit_per_member){
                return $this->sendError('You have already exceeded the limit for this achievement, You cannot claim it at the moment.', [], 400);
            }
            MemberRewardClaim::create([
                'date_claimed' => now(),
                'member_id' => $member->id,
                'reward_id' => $reward->id,
                "status" =>  1
            ]);
            $mailObject = new \stdClass();
            $mailObject->member = $member;
            $mailObject->reward = $reward;
            $mailObject->admin = false;
            Mail::to($member->email)->send(new RewardsClaimed($mailObject));
            $mailObject->admin = true;
            //replace admin email;
            Mail::to($member->email)->send(new RewardsClaimed($mailObject));
            return $this->sendResponse(new MemberResource($member) , 'Reward redeemed successfully');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getProgramByLocations(){
        try {
            $member = Auth::user()->member;
            $locationIds = $member->locations->pluck('id')->unique()->toArray();

            $reservations = Reservation::with('session')->where('member_id', $member->id)->where('status', Reservation::ACTIVE)->get();
            $programIds = $reservations->pluck('session.program_id')->unique()->toArray();
            
            $programs = Program::whereNotIn('id', $programIds)->whereIn('location_id', $locationIds)->paginate(25);

            $data = [
                'programs' => ProgramResource::collection($programs),
                'pagination' => [
                    'current_page' => $programs->currentPage(),
                    'per_page' => $programs->perPage(),
                    'total' => $programs->total(),
                    'prev_page_url' => $programs->previousPageUrl(),
                    'next_page_url' => $programs->nextPageUrl(),
                    'first_page_url' => $programs->url(1),
                    'last_page_url' => $programs->url($programs->lastPage()),
                ],
            ];

            return $this->sendResponse($data, 'Get programs related to location where member is not enrolled.');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function enrollInProgram($programId){
        try{
            $program = Program::find($programId);

            if(!$program){
                return $this->sendError('Program does not exist. Cannot enroll in this program.', [], 400);
            }

            $member = Auth::user()->member;
            $session = $program->ProgramLevels[0]->sessions[0];
            
            DB::beginTransaction();

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

            DB::commit();
            return $this->sendResponse("Success", "Congratulations you have successfully enrolled in ". $program->name ." program.");

        }catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function addChildren(Request $request){
        $member = Auth::user()->member;
        if($member->parent_id){
            return $this->sendError('You need to be a parent to add a children under your account.', [], 500);
        } else {
            try {
                DB::beginTransaction();
                Member::whereIn('id', $request->memberIds)->update([
                    "parent_id" => $member->id
                ]);
                DB::commit();
                return $this->sendResponse("Success", "Family Members Added.");
            } catch(Exception $e){
                DB::rollBack();
                return $this->sendError($e->getMessage(), [], 500);
            }
        }
    }

    public function getChildren(){
        $children = Auth::user()->member->children;
        return $this->sendResponse($children, "Family Members.");
    }

    public function removeChildren(Request $request){
        $member = Auth::user()->member;
        if($member->parent_id){
            return $this->sendError('You need to be a parent to remove a children under your account.', [], 500);
        } else {
            try {
                DB::beginTransaction();
                Member::whereIn('parent_id', $request->memberIds)->update([
                    "parent_id" => null
                ]);
                DB::commit();
                return $this->sendResponse("Success", "Family Members Removed.");
            } catch(Exception $e){
                DB::rollBack();
                return $this->sendError($e->getMessage(), [], 500);
            }
        }
    }

    public function getProgramsWithPlans(){
        $locationId = request()->locationId;
        $member = Auth::user()->member;
        try{
            $reservations = Reservation::with('session')->where('member_id', $member->id)->where('status', Reservation::ACTIVE)->get();
            $programIds = $reservations->pluck('session.program_id')->unique()->toArray();
            
            $programs = Program::with(['stripePlans.pricing'])->whereNotIn('id', $programIds)->where('location_id', $locationId)->get();
            $data = [
                'programs' => ProgramResource::collection($programs),
            ];
            return $this->sendResponse($data, 'Programs with Plans');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getPlanContract(){
        
        try {
            $planId = request()->planId;
            $stripePlan = StripePlan::find($planId);
            if ($stripePlan) {
                $contracts = $stripePlan->contract;
                return $this->sendResponse($contracts, 'Contract');
            }
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getPlan($planId){
        try{
            $member = Auth::user()->member;
            $memberContract = MemberContract::where(['member_id' => $member->id, 'stripe_plan_id' => $planId, 'signed' => true])->firstOrFail();
            if($memberContract) {
                $planId = request()->planId;
                $stripePlan = StripePlan::with('pricing')->find($planId);
                return $this->sendResponse($stripePlan, 'Programs with Plans');
            }
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function fetchVendorStripePk(Request $request){
        try {
            $program = Program::with(['location'])->where('id', $request->programId)->first();
            $location = $program->location;
            Log::info($location->stripe_oauth);
            $stripeDetails = json_decode($location->stripe_oauth);
            return $this->sendResponse($stripeDetails->stripe_publishable_key, 'Location');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function register(Request $request){
        $program = Program::with(['programLevels', 'location'])->where('id', $request->programId)->first();
        $addMember = VendorMemberController::createMemberFromRegistration($request, $program->location, $program->programLevels[0]->id);
        Log::info($addMember);
        return $this->sendResponse($addMember, 'Register');
    }
}
