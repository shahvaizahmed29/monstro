<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Member\SessionResource;
use App\Models\Member;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;

class SessionController extends BaseController
{
    public function getSessionCheckIns($session_id){
        
        $sessions = Session::with(['reservations.checkIns'])
            ->whereHas('reservations.checkIns', function($q){
                $q->where('member_id', Auth::user()->member->id);
            })
            ->where('id', $session_id)
            ->first();
        
        return $this->sendResponse(new SessionResource($sessions), 'Success');
    }

}