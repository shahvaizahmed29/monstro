<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Vendor\NewRewardResource;
use App\Models\Reward;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardController extends BaseController
{

    public function index()
    {
        try {
            $rewards = Reward::with(['achievement']);

            if (isset(request()->type)) {
                if (request()->type == 0) {
                    $rewards = $rewards->whereNotNull('deleted_at')->withTrashed();
                }
            }
            $rewards = $rewards->paginate(25);
            $data = [
                'rewards' => NewRewardResource::collection($rewards),
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
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $location = request()->location;
            $rewardData = [
                'name' => $request->name,
                'description' => $request->description,
                'limit_per_member' => $request->limitPerMember,
                'images' => $request->images,
                'icon' => $request->icon,
                'location_id' => $location->id,
                'required_points' => $request->requiredPoints,
                
            ];
            if ($request->achievementId){
                $rewardData['achievement_id'] = $request->achievementId;
            }
            
            DB::beginTransaction();
            $reward = Reward::create($rewardData);
            DB::commit();
            return $this->sendResponse(new NewRewardResource($reward), 'Reward created successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function show($id)
    {
        try {
            $reward = Reward::with(['achievement'])->find($id);
            
            if (!$reward) {
                return $this->sendError('Reward not found', [], 400);
            }

            return $this->sendResponse(new NewRewardResource($reward), 'Reward fetched successfully');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $reward = Reward::find($id);
            $location = request()->location;

            if (!$reward) {
                return $this->sendError('Reward not found', [], 400);
            }
            if (!$location) {
                return $this->sendError('Location not found', [], 400);
            }
            DB::beginTransaction();
            $rewardData = [
                'name' => $request->name,
                'description' => $request->description,
                'limit_per_member' => $request->limitPerMember,
                'images' => $request->images,
                'icon' => $request->icon,
                'location_id' => $location->id,
                'required_points' => $request->requiredPoints,
                
            ];
            if ($request->achievementId){
                $rewardData['achievement_id'] = $request->achievementId;
            }
            $reward->update($rewardData);
            DB::commit();
            $reward = Reward::with('achievement')->where('id', $reward->id)->first();

            return $this->sendResponse(new NewRewardResource($reward), 'Reward updated successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reward = Reward::find($id);

            if (!$reward) {
                return $this->sendError('Reward not found', [], 400);
            }
            DB::beginTransaction();
            $reward->delete();
            DB::commit();
            return $this->sendResponse('Success', 'Reward deleted successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
