<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\ProgramLevel;
use App\Models\Session;
use App\Models\Location;
use App\Models\Setting;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Vendor\MemberController;
use App\Http\Requests\ProgramLevelRequest;
use App\Http\Requests\ProgramLevelUpdateRequest;
use App\Http\Requests\ProgramStoreRequest;
use App\Http\Requests\ProgramUpdateRequest;
use App\Http\Resources\Member\CheckInResource;
use App\Http\Resources\Vendor\ProgramLevelResource;
use App\Http\Resources\Vendor\ProgramResource;
use App\Models\CheckIn;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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


    public function lastTenAttendance($member_id, $program_id){
        try{
            $attendances = CheckIn::whereHas('reservation', function($query) use ($member_id){
                $query->where('member_id', $member_id);
            })
            ->whereHas('reservation.session', function($query) use ($program_id){
                $query->where('program_id', $program_id);
            })
            ->with(['reservation.session'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

            return $this->sendResponse(CheckInResource::collection($attendances), 'Get attendances related to member and program.');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }
    
    public function getProgramById($id){
        try{
            $location = request()->location;
            $program = Program::with('programLevels')->where('id',$id)->where('location_id', $location->id)->first();

            if(!$program){
                return $this->sendError("Program doesnot exist", [], 400);
            }
            //Code commented out below becuase auth guard is not applied anymore.
            // $location = $program->location;
            // if($location->vendor_id != auth()->user()->vendor->id) {
            //     return $this->sendError('Vendor not authorize, Please contact admin', [], 403);
            // }
            return $this->sendResponse(new ProgramResource($program), 'Get programs related to specific location');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
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
                
                $program_level->save();
                
                $parent_id = $program_level->id;

                $tomorrow = Carbon::tomorrow();
                $formattedTomorrow = $tomorrow->format('Y-m-d');

                $twoYearsLater = $tomorrow->addYears(2);
                $twoYearsLaterformattedDate = $twoYearsLater->format('Y-m-d');

                $session = Session::create([
                    'program_id' => $program->id,
                    'program_level_id' => $program_level->id,
                    'duration_time' => $session['duration_time'],
                    'start_date' => $formattedTomorrow,
                    'end_date' => $twoYearsLaterformattedDate,
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

    public function addProgramLevel(ProgramLevelRequest $request){
        try{
            DB::beginTransaction();
            $parent_id = null;
            $programId = $request->program_id;
            $oldProgramLevelId = ProgramLevel::where('program_id', $programId)->latest()->first();
            
            if($oldProgramLevelId){
                $parent_id = $oldProgramLevelId->id;
            }

            $program_level = ProgramLevel::create([
                'name' => $request->name,
                'program_id' => $programId,
                'parent_id' => $parent_id,
                'capacity' => $request->capacity,
                'min_age' => $request->min_age,
                'max_age' => $request->max_age,
            ]);

            $program_level->save();

            foreach($request->sessions as $session){
                $tomorrow = Carbon::tomorrow();
                $formattedTomorrow = $tomorrow->format('Y-m-d');

                $twoYearsLater = $tomorrow->addYears(2);
                $twoYearsLaterformattedDate = $twoYearsLater->format('Y-m-d');

                $session = Session::create([
                    'program_id' => $programId,
                    'program_level_id' => $program_level->id,
                    'duration_time' => $session['duration_time'],
                    'start_date' => $formattedTomorrow,
                    'end_date' => $twoYearsLaterformattedDate,
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
            
            return $this->sendResponse(new ProgramLevelResource($program_level), 'Program Level created successfully.');
        }catch(Exception $e){
            DB::rollBack();
            Log::info('===== ProgramController - addProgramLevel() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function programLevelArchive($programLevelId){
        try{ 
            $programLevel = ProgramLevel::find($programLevelId);

            if(!$programLevel){
                return $this->sendError('Program level does not exist.', [], 400);
            }

            $programLevel->status = \App\Models\ProgramLevel::ARCHIVED;
            $programLevel->save();

            return $this->sendResponse(new ProgramLevelResource($programLevel), 'Program level archived successfully.');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
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
        try{
            $avatar = $request->file('avatar');
            $imgPath = 'program-images/';
            $imageUrl = null;

            if($avatar){
                $fileName = $program->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatar->move(public_path($imgPath), $fileName);
                $imageUrl = getenv('APP_URL') .$imgPath. $fileName;
            }

            DB::beginTransaction();

            $program->update([
                'name' => $request->name,
                'description' => $request->description,
                'avatar' => isset($imageUrl) ? $imageUrl : $program->avatar,
            ]);

            DB::commit();
            return $this->sendResponse(new ProgramResource($program), 'Program updated successfully.');
        }catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ProgramController - programUpdate() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function programLevelUpdate(ProgramLevelUpdateRequest $request, ProgramLevel $programLevel){
        try{
            DB::beginTransaction();

            $programLevel->update([
                'program_id' => $programLevel->program->id,
                'name' => $request->name,
                'capacity' => $request->capacity,
                'min_age' => $request->min_age,
                'max_age' => $request->max_age
            ]);
            
            foreach ($request->sessions as $session) {
                $sessionId = isset($session['id']) ? $session['id'] : null;
                if(isset($session['duration_time']) && isset($session['start_date']) && isset($session['end_date'])){
                    Session::updateOrCreate(
                        ['id' => $sessionId],
                        [
                            'program_level_id' => $programLevel->id,
                            'program_id' => $programLevel->program->id,
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
            return $this->sendResponse(new ProgramLevelResource($programLevel), 'Program level updated successfully.');
        }catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ProgramController - programLevelUpdate() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function oldUpdate(ProgramUpdateRequest $request, Program $program){
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
                foreach ($programLevel->sessions as $sessions) {
                    foreach ($sessions->reservations as $reservation) {
                        if ($reservation->status === \App\Models\Reservation::ACTIVE) {
                            return $this->sendError("Cannot delete program. Active reservations exist in the program sessions.", [], 400);
                        }
                    }
                }
            }

            $program->delete();
            return $this->sendResponse("Success", "Program deleted successfully");
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function deleteProgramLevel($programLevelId){
        try{
            $programLevel = ProgramLevel::with('sessions.reservations')->find($programLevelId);
            
            if(!$programLevel){
                return $this->sendError("Program Level not exist", [], 400);
            }

            foreach ($programLevel->sessions as $sessions) {
                foreach ($sessions->reservations as $reservation) {
                    if ($reservation->status === \App\Models\Reservation::ACTIVE) {
                        return $this->sendError("Cannot delete program level. Active reservations exist in the program level sessions.", [], 400);
                    }
                }
            }

            $programLevel->delete();
            return $this->sendResponse("Success", "Program Level deleted successfully");
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function syncMembersByLocation($programId) {
        $delayTimeForEachLocation = 60;
        $reqCustomField = null;
        $location = request()->location;
        $program = Program::with('programLevels')->where('id',$programId)->first();
        $ghl_integration = Setting::where('name', 'ghl_integration')->first();
        $token = $ghl_integration['value'];
        $companyId = $ghl_integration['meta_data']['companyId'];
        if((Carbon::now()->diffInMinutes($program->last_sync_at) >= $delayTimeForEachLocation) || !$program->last_sync_at) {
            try {
                $tokenObj = Http::withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'Version' => '2021-07-28'                
                ])->asForm()->post('https://services.leadconnectorhq.com/oauth/locationToken', [
                    'companyId' => $companyId,
                    'locationId' => $location->go_high_level_location_id,
                ]);
        
                if ($tokenObj->failed()) {
                    return $this->sendError('Something went wrong!', json_encode($tokenObj->json()));
                }
    
                $tokenObj = $tokenObj->json();
                
                $responseCustomField = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$tokenObj['access_token'],
                    'Version' => '2021-07-28'
                ])->get('https://services.leadconnectorhq.com/locations/'.$location->go_high_level_location_id.'/customFields');
    
                if ($responseCustomField->failed()) {
                    $responseCustomField->throw();    
                }
                $responseCustomField = $responseCustomField->json();
    
                foreach($responseCustomField['customFields'] as $customField) {
                    if($customField['name'] == 'Program Level') {
                        $reqCustomField = $customField;
                    }
                }
    
                if($reqCustomField) {
                    $url = 'https://services.leadconnectorhq.com/contacts/?locationId='.$location->go_high_level_location_id.'&limit=100';
                    do {
                        $response = Http::withHeaders([
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer '.$tokenObj['access_token'],
                            'Version' => '2021-07-28'
                        ])->get($url);
            
                        if ($response->failed()) {
                            $response->throw();    
                        }
                        $response = $response->json();
                        $contacts = $response['contacts'];
                        $url = null;
                        if(isset($response['meta'])) {
                            if(isset($response['meta']['nextPageUrl'])) {
                                $url = $response['meta']['nextPageUrl'];
                                $url = str_replace('http://', 'https://', $url);
                            }
                        }
                        foreach($contacts as $contact) {
                            $programLevelId = null;
                            foreach($contact['customFields'] as $customField) {
                              
                                if($customField['id'] == $reqCustomField['id']) {
                                    if (strpos($customField['value'], '_') === false) {
                                        continue;
                                    }
                                    $parts = explode('_', $customField['value']);
                                    if(count($parts) != 2) {
                                        continue;
                                    }
    
                                    $programLevelName = $parts[1];
                                    $programName = $parts[0];
    
                                    if($programName == $program->name) {
                                        foreach($program->programLevels as $programLevel) {
                                            if($programLevelName == $programLevel->name) {
                                                $programLevelId = $programLevel->id;
                                                MemberController::createMemberFromGHL($contact, $location ,$programLevelId);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } while($url);
                }
                $program->last_sync_at = now();
                $program->save();
    
            } catch(\Exception $error) {
                return $this->sendError('Something went wrong!', $error->getMessage());
            }
            return $this->sendResponse([], 'Members synced successfully');
        } else {
            return $this->sendError('Resync again in about '. $delayTimeForEachLocation - Carbon::now()->diffInMinutes($program->last_sync_at).' mins', []);
        }
    }

    public function addMemberManually($programLevelId, Request $request){
        try {
            $reqCustomField = null;
            $location = request()->location;
            $programLevel = ProgramLevel::with('program')->where('id',$programLevelId)->first();
            // $ghl_integration = Setting::where('name', 'ghl_integration')->first();
            // $token = $ghl_integration['value'];
            // $companyId = $ghl_integration['meta_data']['companyId'];

            // $tokenObj = Http::withHeaders([
            //     'Authorization' => 'Bearer '.$token,
            //     'Version' => '2021-07-28'                
            // ])->asForm()->post('https://services.leadconnectorhq.com/oauth/locationToken', [
            //     'companyId' => $companyId,
            //     'locationId' => $location->go_high_level_location_id,
            // ]);
    
            // if ($tokenObj->failed()) {
            //     return $this->sendError('Something went wrong!', json_encode($tokenObj->json()));
            // }

            // $tokenObj = $tokenObj->json();
            
            // $responseCustomField = Http::withHeaders([
            //     'Accept' => 'application/json',
            //     'Authorization' => 'Bearer '.$tokenObj['access_token'],
            //     'Version' => '2021-07-28'
            // ])->get('https://services.leadconnectorhq.com/locations/'.$location->go_high_level_location_id.'/customFields');

            // if ($responseCustomField->failed()) {
            //     $responseCustomField->throw();    
            // }
            // $responseCustomField = $responseCustomField->json();

            // foreach($responseCustomField['customFields'] as $customField) {
            //     if($customField['name'] == 'Program Level') {
            //         $reqCustomField = $customField;
            //     }
            // }

            // if($reqCustomField) {
            //     $contact = $request;
            //     $programLevelId = null;
            //     foreach($contact['customFields'] as $customField) {
                    
            //         if($customField['id'] == $reqCustomField['id']) {
            //             if (strpos($customField['value'], '_') === false) {
            //                 continue;
            //             }
            //             $parts = explode('_', $customField['value']);
            //             if(count($parts) != 2) {
            //                 continue;
            //             }

            //             $programLevelName = $parts[1];
            //             $programName = $parts[0];

            //             if($programName == $program->name) {
            //                 foreach($program->programLevels as $programLevel) {
            //                     if($programLevelName == $programLevel->name) {
            //                         $programLevelId = $programLevel->id;
            //                         MemberController::createMemberFromGHL($contact, $location ,$programLevelId);
            //                     }
            //                 }
            //             }
            //         }
            //     }
            // }

            $contact = $request->all();
            if(!isset($contact['email'])) {
                return $this->sendError('No email found against the contact!', json_encode($tokenObj->json()));
            } else {
                MemberController::createMemberFromGHL($contact, $location ,$programLevelId);
            }

            return $this->sendResponse('Success', 'Member synced successfully');
        } catch(Exception $error) {
            return $this->sendError('Something went wrong!', $error->getMessage());
        }

    }

}
