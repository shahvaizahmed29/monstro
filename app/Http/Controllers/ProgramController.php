<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgramsResource;
use App\Models\Member;
use App\Models\ProgramLevel;
use Illuminate\Http\Request;

class ProgramController extends BaseController
{
    public function getSessions($program_id){
        $program_level = ProgramLevel::with(['sessions.reservations.checkIns'])
            ->where('program_id', $program_id)
            ->get();

        return $program_level;
    }

}
