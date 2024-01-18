<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Vendor\AchievementResource;
use App\Models\Achievement;
use Exception;
use Illuminate\Http\Request;

class AchievementController extends BaseController
{
    public function createAchievement(Request $request){
        try{
            $achievement = Achievement::create($request);
            return $this->sendResponse(new AchievementResource($achievement), 'Achievement created successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getAchievement($id){
        try{
            $achievement = Achievement::findOrFail($id);
            return $this->sendResponse(new AchievementResource($achievement), 'Achievement retrieved successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
