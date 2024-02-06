<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\AchievementResource;
use App\Models\Achievement;
use App\Models\AchievementActions;
use App\Models\AchievementRequirements;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AchievementController extends BaseController
{
    public function createAchievement(Request $request){
        try{
            $achievement = Achievement::create($request->all());
            AchievementActions::create(['action_id' => $request->action_id, 'count' => $request->action_count, 'achievement_id' => $achievement->id]);
    
            $achievement = Achievement::with(['actions'])->find($achievement->id);

            return $this->sendResponse(new AchievementResource($achievement), 'Achievement created successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getAchievement($id){
        try{
            $achievement = Achievement::with(['actions'])->find($id);

            if(!$achievement){
                return $this->sendError('Achievement not found', [], 400);
            }

            return $this->sendResponse(new AchievementResource($achievement), 'Achievement fetched successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }
}
