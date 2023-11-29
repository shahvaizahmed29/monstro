<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Location;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\MemberResource;

class MemberController extends BaseController
{
    public function getMembersByLocation($location_id){
        $location = Location::find($location_id);
        if($location->vendor_id != auth()->user()->vendor->id) {
            return $this->sendError('Vendor not authenticated', [], 401);
        }
        $members = Member::whereHas('locations', function ($query) use ($location_id) {
            $query->where('locations.id', $location_id);
        })->get();
        return $this->sendResponse(MemberResource::collection($members), 'Location members');
    }

}
