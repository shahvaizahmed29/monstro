<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\BaseController;
use App\Models\ProgramLevel;

class ProgramController extends BaseController
{
    public function getSessions($program_id){
        $program_level = ProgramLevel::with(['sessions.reservations.checkIns'])
            ->where('program_id', $program_id)
            ->get();
        return $program_level;
    }

}
