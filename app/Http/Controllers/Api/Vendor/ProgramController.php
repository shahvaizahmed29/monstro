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
use App\Http\Requests\ProgramUpdateRequest;
use App\Http\Resources\Vendor\ProgramResource;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        if(!$program){
            return $this->sendError("Program not found", [], 400);
        }

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
            DB::beginTransaction();
            $program = Program::create([
                'location_id' => $location->id,
                'name' => $request->program_name,
                'description' => $request->description,
                'avatar' => $request->avatar ?? null,
            ]);

            $parent_id = null;
            foreach($request->sessions as $session){
                $program_level = ProgramLevel::create([
                    'name' => $session['program_level_name'],
                    'program_id' => $program->id,
                    'parent_id' => $parent_id,
                    'capacity' => $session['capacity'],
                    'min_age' => $session['min_age'],
                    'max_age' => $session['max_age'],
                ]);
                
                $program_level->custom_field_ghl_value = $program->name . '_' . $program_level->id;
                $program_level->save();
                

                $parent_id = $program_level->id;

                $session = Session::create([
                    'program_id' => $program->id,
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
            DB::commit();
            
            $program = Program::with('programLevels.sessions')->where('id', $program->id)->first();
            return $this->sendResponse(new ProgramResource($program), 'Program created successfully.');
        }catch(Exception $e){
            DB::rollBack();
            Log::info('===== ProgramController - addProgram() - error =====');
            Log::info($e->getMessage());
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

            $data = [
                'totalStudents' => $program->totalStudents(),
                'totalActiveStudents' => $program->totalActiveStudents(),
                'monthlyRetentionRate' => $program->monthlyRetentionRate()
            ];

            return $this->sendResponse($data, 'Program related information related to program and location.');
        }catch(Exception $e){
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function programLevelActiveSessions($programLevelId){
        try{
            $programLevel = ProgramLevel::where('id', $programLevelId)->first();
            $formated_sessions = $programLevel->getFormattedSessions();
            return $formated_sessions;
        }catch(Exception $e){
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function update(ProgramUpdateRequest $request, Program $program){
        try {
            DB::beginTransaction();
            $program->update([
                'name' => $request->program_name,
                'description' => $request->description,
                'avatar' => $request->avatar ?? $program->avatar,
            ]);

            foreach ($request->sessions as $session) {
                $programLevelId = isset($session['program_level_id']) ? $session['program_level_id'] : null;
                $programLevel = ProgramLevel::updateOrCreate(
                    ['id' => $programLevelId],
                    [
                        'program_id' => $program->id,
                        'name' => $session['program_level_name'],
                        'capacity' => $session['capacity'],
                        'min_age' => $session['min_age'],
                        'max_age' => $session['max_age']
                    ]
                );

                $programLevel->save();

                $sessionId = isset($session['id']) ? $session['id'] : null;
                if(isset($session['duration_time']) && isset($session['start_date']) && isset($session['end_date'])){
                    Session::updateOrCreate(
                        ['id' => $sessionId],
                        [
                            'program_level_id' => $programLevel->id,
                            'program_id' => $program->id,
                            'duration_time' => $session['duration_time'],
                            'start_date' => $session['start_date'],
                            'end_date' => $session['end_date'],
                            'monday' => isset($session['monday']) ? $session['monday'] : null,
                            'tuesday' => isset($session['tuesday']) ? $session['tuesday'] : null,
                            'wednesday' => isset($session['wednesday']) ? $session['wednesday'] : null,
                            'thursday' => isset($session['thursday']) ? $session['thursday'] : null,
                            'friday' => isset($session['friday']) ? $session['friday'] : null,
                            'saturday' => isset($session['saturday']) ? $session['saturday'] : null,
                            'sunday' => isset($session['sunday']) ? $session['sunday'] : null,
                        ]
                    );
                }
            }

            DB::commit();
            $program = Program::with('programLevels.sessions')->where('id', $program->id)->first();
            return $this->sendResponse(new ProgramResource($program), 'Program updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ProgramController - updateProgram() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function delete($programId){
        try{
            $program = Program::with('programLevels.sessions')->find($programId);
            
            if(!$program){
                return $this->sendError("Program not found", [], 400);
            }

            foreach ($program->programLevels as $programLevel) {
                foreach ($programLevel->sessions as $session) {
                    if ($session->status === \App\Models\Session::ACTIVE) {
                        return $this->sendError("Cannot delete program. Active sessions exist in the program.", [], 400);
                    }
                }
            }

            $program->delete();
            return $this->sendResponse("Success", "Program deleted successfully");
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
