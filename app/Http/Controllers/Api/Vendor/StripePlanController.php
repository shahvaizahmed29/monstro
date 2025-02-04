<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Models\MemberPlan;
use App\Http\Controllers\BaseController;
use App\Models\Program;
use Exception;

class StripePlanController extends BaseController
{
    public function getPlans($programId){
      try{
        $program = Program::with('location')->where('id',$programId)->first();
        $location = $program->location;
        if(!$location){
            return $this->sendError("Location doesnot exist", [], 400);
        }
        $plans = MemberPlan::with('pricing')->where('vendor_id', $location->vendor_id)->get();

        return $this->sendResponse($plans, 'Plans List');

    }catch(Exception $error){
        return $this->sendError($error->getMessage(), [], 500);
    }
  }

  public function getPlan($planId){
    try{
      $plans = MemberPlan::with('pricing')->where('id', $planId)->firstOrFail();

      return $this->sendResponse($plans, 'Plan');

  }catch(Exception $error){
      return $this->sendError($error->getMessage(), [], 500);
  }
}
}

