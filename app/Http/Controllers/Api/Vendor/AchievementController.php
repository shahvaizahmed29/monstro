<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Vendor\AchievementResource;
use App\Models\Achievement;
use App\Models\Reward;
use App\Models\AchievementActions;
use App\Models\Location;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchievementController extends BaseController
{
    public function index(){
        try{
            $achievements = Achievement::with(['actions', 'members']);
            
            if(isset(request()->type)) {
                if(request()->type == 0) {
                    $achievements = $achievements->whereNotNull('deleted_at')->withTrashed();
                }
            }
            
            $achievements = $achievements->paginate(25);
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
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function create(Request $request){
        $location = request()->location;
        $location = Location::find($location->id);
        if(!$location) {
            return $this->sendError("Location Not found", [], 500);
        }
        try{
            DB::beginTransaction();
            $achievement = Achievement::create([
                "name" => $request->name,
                "badge" => $request->badge,
                "points" => $request->points,
                "location_id" => $location->id
            ]);
            Log::info(json_encode($achievement));
            AchievementActions::create(['action_id' => $request->action, 'count' => $request->actionCount, 'achievement_id' => $achievement->id]);

            if($request->program) {
                $achievement->update([
                    'program_id' => $request->program
                ]);
            }
            DB::commit();

            $achievement = Achievement::with(['actions'])->find($achievement->id);

            return $this->sendResponse(new AchievementResource($achievement), 'Achievement created successfully');
        }catch(Exception $error){
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getAchievement($id){
        try{
            $achievement = Achievement::with(['actions', 'members'])->find($id);

            if(!$achievement){
                return $this->sendError('Achievement not found', [], 400);
            }

            return $this->sendResponse(new AchievementResource($achievement), 'Achievement fetched successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function update(Request $request, Achievement $achievement){
        try{
            DB::beginTransaction();
            $achievement->update([
                'name' => $request->name,
                'badge' => $request->badge ?? $achievement->badge,
                'points' => $request->points ?? $achievement->points,
            ]);

            DB::commit();
            return $this->sendResponse(new AchievementResource($achievement), 'Achievement updated successfully');
        }catch(Exception $error){
            DB::rollBack();
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function delete($id){
        try{
            DB::beginTransaction();
            $achievement = Achievement::find($id);

            if(!$achievement){
                return $this->sendError('Achievement not found', [], 400);
            }

            $achievement->delete();

            Reward::where('achievement_id', $achievement->id)->delete();
            DB::commit();
            return $this->sendResponse('Success', 'Achievement deleted successfully');
        }catch(Exception $error){
            DB::rollBack();
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
