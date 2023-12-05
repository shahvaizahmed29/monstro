<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Location;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Member\ReservationResource;
use App\Http\Resources\Vendor\MemberResource;
use App\Models\Reservation;

class MemberController extends BaseController
{
    public function getMembersByLocation($location_id){
        $location = Location::find($location_id);
        if($location->vendor_id != auth()->user()->vendor->id) {
            return $this->sendError('Vendor not authenticated', [], 403);
        }
        $members = Member::whereHas('locations', function ($query) use ($location_id) {
            $query->where('locations.id', $location_id);
        })->get();
        return $this->sendResponse(MemberResource::collection($members), 'Location members');
    }

    public function getMemberDetails($member_id){
        $reservations = Reservation::with(['session', 'session.programLevel','session.programLevel.program'])->where('member_id', $member_id);
        $member_details = Member::where('id', $member_id)->first();
        $data = [
            'memberDetails' => new MemberResource($member_details),
            'reservations' => ReservationResource::collection($reservations)
        ];
        return $this->sendResponse($data, 'Member details with session reservations and program');
    }

    public static function createMemberFromGHL($contact) {
        \Log::info(json_encode($contact));
    }
}
