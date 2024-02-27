<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Resources\Member\ClaimedRewardResource;
use App\Http\Resources\Member\GetMemberProfile;
use App\Http\Resources\Member\MemberResource;
use App\Http\Resources\Vendor\AchievementResource;
use App\Models\Achievement;
use App\Models\Member;
use App\Models\MemberRewardClaim;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MemberController extends BaseController
{
    public function profileUpdate(Request $request, $userId){
        try{
            $user = User::find($userId);

            if(!$user){
                return $this->sendError('User not exist.', [], 400);
            }

            $user->name = isset($request->name) ? $request->name : $user->name;
            $user->save();
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

}
