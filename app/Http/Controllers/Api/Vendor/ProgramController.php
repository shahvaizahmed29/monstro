<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\ProgramLevel;
use App\Models\Session;
use App\Models\Location;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramStoreRequest;
use App\Http\Resources\Vendor\ProgramResource;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class ProgramController extends BaseController
{
    public function getProgramsByLocation(){
        $location = request()->location;
        //Code commented out below becuase auth guard is not applied anymore.
        // if($location->vendor_id != auth()->user()->vendor->id) {
        //     return $this->sendError('Vendor not authorize, Please contact admin', [], 403);
        // }
        $programs = Program::where('location_id', $location->id)->paginate(25);
        $data = [
            'programs' => ProgramResource::collection($programs),
            'pagination' => [
                'current_page' => $programs->currentPage(),
                'per_page' => $programs->perPage(),
                'total' => $programs->total(),
                'prev_page_url' => $programs->previousPageUrl(),
                'next_page_url' => $programs->nextPageUrl(),
                'first_page_url' => $programs->url(1),
                'last_page_url' => $programs->url($programs->lastPage()),
            ],
        ];
        return $this->sendResponse($data, 'Get programs related to specific location');
    }
    
    public function getProgramById($id){
        $location = request()->location;
        $program = Program::with('programLevels')->where('id',$id)->where('location_id', $location->id)->first();
        //Code commented out below becuase auth guard is not applied anymore.
        // $location = $program->location;
        // if($location->vendor_id != auth()->user()->vendor->id) {
        //     return $this->sendError('Vendor not authorize, Please contact admin', [], 403);
        // }
        return $this->sendResponse(new ProgramResource($program), 'Get programs related to specific location');
    }

    public function addProgram(ProgramStoreRequest $request){
        $location = request()->location;
        try{
            //Code commented out below becuase auth guard is not applied anymore.
            // $location = Location::find($request->location_id);
            // if($location->vendor_id != auth()->user()->vendor->id) {
            //     return $this->sendError('Vendor not authorize, Please contact admin', [], 403);
            // }
            $program = Program::create([
                'location_id' => $location->id,
                // 'custom_field_ghl_id' => $request->custom_field_ghl_id,
                'name' => $request->program_name,
                'description' => $request->description,
                'avatar' => $request->avatar ?? null,
            ]);

            $parent_id = null;
            // $randomNumberMT = mt_rand(10000, 99999);
            foreach($request->sessions as $session){
                $program_level = ProgramLevel::create([
                    'name' => $session['program_level_name'],
                    'program_id' => $program->id,
                    'parent_id' => $parent_id,
                    'capacity' => $session['capacity'],
                    'min_age' => $session['min_age'],
                    'max_age' => $session['max_age'],
                ]);
                
                $program_level->custom_field_ghl_value = $program->id . '_' . $program_level->id;
                $program_level->save();
                

                $parent_id = $program_level->id;

                $session = Session::create([
                    'program_level_id' => $program_level->id,
                    'duration_time' => $session['duration_time'],
                    'start_date' => $session['start_date'],
                    'end_date' => $session['end_date'],
                    'monday' => $session['monday'] ?? null,
                    'tuesday' => $session['tuesday'] ?? null,
                    'wednesday' => $session['wednesday'] ?? null,
                    'thursday' => $session['thursday'] ?? null,
                    'friday' => $session['friday'] ?? null,
                    'saturday' => $session['saturday'] ?? null,
                    'sunday' => $session['sunday'] ?? null,
                    'status' => \App\Models\Session::ACTIVE
                ]);
            }
            $program = Program::with('programLevels.sessions')->where('id', $program->id)->first();
            return $this->sendResponse(new ProgramResource($program), 'Program created successfully.');
        }catch(Exception $e){
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getProgramDetails($programId){
        try{
            $location = request()->location;
            $program = Program::with('members')->where('id', $programId)->where('location_id', $location->id)->first();

            if(!$program){
                return $this->sendError('Program does not exist.', [], 400);
            }

            $totalStudents = $program->totalStudents();
            $totalActiveStudents = $program->totalActiveStudents();
            $monthlyRetentionRate = $program->monthlyRetentionRate();

            // foreach ($program->programLevels as $programLevel) {
            //     foreach ($programLevel->sessions as $session) {
            //         $totalEnrolledStudentsCount += $session->reservations()->where('status', \App\Models\Reservation::ACTIVE)->count();
            //     }
            // }

            // $ProgramLevel = $program->programLevels()->first();
         
            // ($ProgramLevel) ? $totalSessionCount = $ProgramLevel->sessions()->count() : 0; 

            // $activeStudentsCount = Program::where('id', $programId)
            //     ->with([
            //         'programLevels.sessions.reservations' => function ($query) {
            //             $query->whereHas('session', function ($sessionQuery) {
            //                 $sessionQuery->where('status', Session::ACTIVE);
            //             });
            //         },
            //     ])
            //     ->first()
            //     ->programLevels
            //     ->flatMap(function ($programLevel) {
            //         return $programLevel->sessions->flatMap(function ($session) {
            //             return $session->reservations->pluck('member_id');
            //         });
            //     })
            //     ->unique()
            //     ->count();
            
            // $data = [
            //     'totalEnrolledStudentsCount' => $totalEnrolledStudentsCount,
            //     'activeStudentsCount' => $activeStudentsCount,
            //     'totalSessionCount' => $totalSessionCount
            // ];

            $data = [
                'totalStudents' => $totalStudents,
                'totalActiveStudents' => $totalActiveStudents,
                'monthlyRetentionRate' => $monthlyRetentionRate
            ];

            return $this->sendResponse($data, 'Program related information related to program and location.');
        }catch(Exception $e){
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function programLevelActiveSessions($programLevelId){
        try{
            $programLevel = ProgramLevel::where('id', $programLevelId)->first();
            $activeSessions = $programLevel->activeSessions();
            $formated_sessions = [];

            $startTime = Carbon::parse($activeSessions->start_date)->addHours($activeSessions->time);
            $endTime = $startTime->copy()->addHours($activeSessions->duration_time);
            $formated_sessions[] = [
                'title' => $programLevel->program->name,
                'start' => $startTime->format('Y-m-d\TH:i:s'),
                'end' => $endTime->format('Y-m-d\TH:i:s'),
            ];

            return $formated_sessions;
        }catch(Exception $e){
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
