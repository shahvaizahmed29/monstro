<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Mail\StaffRegistration;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StaffController extends BaseController
{
    public function store(Request $request)
    {
        $location = request()->location;
        if (!$location) {
            return $this->sendError('Location not found', [], 400);
        }
        $role = Role::findById($request->role, "web");
        if (!$role) {
            return $this->sendError('Role not found', [], 400);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return $this->sendError('User Already Existed', [], 400);
        }
        try {
            $password = "S2" . Str::random(8) . "#$!" . mt_rand(100, 999);
            DB::beginTransaction();
            $user = User::create([
                'name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
            ]);
            $staffData = [
              'first_name' => $request->firstName,
              'last_name' => $request->lastName,
              'email' => $request->email,
              'phone' => $request->phone,
              'avatar' => $request->avatar ?? null,
              'role_id' => $request->role,
              'location_id' => $location->id,
            ];
            $assigned = $user->assignRole(User::STAFF);
            Log::info("Line No: 55 ".json_encode($role));
            $assigned = $user->assignRole($role->name);
            Log::info("Line No: 57 ".json_encode($assigned));
            $staff = $user->staff()->create($staffData);
            Log::info("Line No: 59 ".json_encode($staff));
            DB::commit();
            try {
              Mail::to($request->email)->send(new StaffRegistration(
                $request->first_name,
                $request->last_name,
                $request->email,
                $password
            ));
            } catch (\Exception $error) {
                Log::info('===== Inviting New Staff email =====');
                Log::info(json_encode($request));
                Log::info($error->getMessage());
            }
            return $this->sendResponse($user, 'Staff invited successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $location = request()->location;
            if (!$location) {
                return $this->sendError('Location not found', [], 400);
            }
            $role = Role::findById($request->role, "web");
            if (!$role) {
                return $this->sendError('Role not found', [], 400);
            }
            $staff = Staff::find($id);
            if (!$staff) {
                return $this->sendError('Staff not found', [], 400);
            }
            $user = User::find($staff->user_id);
            DB::beginTransaction();
            $staff->update([
              "location_id" => $location->id
            ]);
            $user->syncRoles([User::STAFF, $role->name]);
            DB::commit();
            return $this->sendResponse($role, 'Staff updated successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function destroy($id)
    {
        try {
          $location = request()->location;
          if (!$location) {
            return $this->sendError('Location not found', [], 400);
          }
          $staff = Staff::find($id);
          if (!$staff) {
              return $this->sendError('Role not found', [], 400);
          }
          $user = User::find($staff->user_id);
          DB::beginTransaction();
          $user->delete();
          $staff->delete();
          DB::commit();
          return $this->sendResponse('Success', 'Role deleted successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
