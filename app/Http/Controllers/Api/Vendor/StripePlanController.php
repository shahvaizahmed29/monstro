<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use App\Services\TimezoneService;
use App\Models\StripePlan;
use App\Models\Location;
use App\Http\Controllers\BaseController;
use Exception;

class StripePlanController extends BaseController
{
    public function getPlans(){
      try{
        $location = request()->location;
        $location = Location::find($location->id);
        if(!$location){
            return $this->sendError("Location doesnot exist", [], 400);
        }
        $plans = StripePlan::with('pricing')->where('vendor_id', $location->vendor_id)->get();

        return $this->sendResponse($plans, 'Plans List');

    }catch(Exception $error){
        return $this->sendError($error->getMessage(), [], 500);
    }
  }
}