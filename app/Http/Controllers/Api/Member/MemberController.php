<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Resources\Member\ClaimedRewardResource;
use App\Http\Resources\Member\GetMemberProfile;
use App\Http\Resources\Member\MemberResource;
use App\Http\Resources\Member\ProgramResource;
use App\Http\Resources\Vendor\AchievementResource;
use App\Models\Achievement;
use App\Models\Location;
use App\Models\Member;
use App\Models\MemberRewardClaim;
use App\Models\Program;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

    public function getMemberRewards(){
        try{
            $rewards = MemberRewardClaim::where('member_id', auth()->user()->member->id)->paginate(25);
            
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
            $redeemPoints = $request->redeemPoints;

            if($request->redeemPoints == 0){
                return $this->sendError('Not enough reedem points to claimed at the moment. You have '.$member->current_points.' points in your account currently', [], 400);
            }elseif($member->current_points >= $request->redeemPoints){
                $currentPoints = $member->current_points - $request->redeemPoints;
                $member->current_points = $currentPoints;
                $member->save();

                MemberRewardClaim::create([
                    'points_claimed' => $redeemPoints,
                    'previous_points' => $currentPoints,
                    'date_claimed' => now(),
                    'member_id' => $member->id
                ]);

                return $this->sendResponse(new MemberResource($member) , 'Points redeem successfully');
            }else{
                return $this->sendError('Not enough reedem points to claimed at the moment. You have '.$member->current_points.' points in your account currently', [], 400);
            }

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

}
