<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionController extends BaseController
{
    public function store(Request $request)
    {
        $location = request()->location;
        if (!$location) {
            return $this->sendError('Location not found', [], 400);
        }
        try {
            $roleData = [
                'name' => $request->name,
                'color' => $request->color,
                'location_id' => $location->id,
                'guard_name' => "web"
            ];
            DB::beginTransaction();
            $role = Role::create($roleData);
            if (!empty($request->permissions)) {
                $permissionNames = $request->permissions;
                $permissions = Permission::whereIn('name', $permissionNames)->get();
                $permissions = $permissions->pluck('id')->toArray();
                $role->syncPermissions($permissions);
            }
            DB::commit();
            return $this->sendResponse($role, 'Role created successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $role = Role::find($id);
            $location = request()->location;

            if (!$role) {
                return $this->sendError('Role not found', [], 400);
            }
            if (!$location) {
                return $this->sendError('Location not found', [], 400);
            }
            DB::beginTransaction();
            $roleData = [
              'name' => $request->name,
              'color' => $request->color,
            ];
            $role->update($roleData);
            if (!empty($request->permissions)) {
              $permissionNames = $request->permissions;
              $permissions = Permission::whereIn('name', $permissionNames)->get();
              $permissions = $permissions->pluck('id')->toArray();
              $role->syncPermissions($permissions);
          } else {
            $role->syncPermissions([]);
          }
            DB::commit();

            return $this->sendResponse($role, 'Role updated successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function destroy($id)
    {
        try {
          $role = Role::find($id);
          $location = request()->location;

          if (!$role) {
              return $this->sendError('Role not found', [], 400);
          }
          if (!$location) {
              return $this->sendError('Location not found', [], 400);
          }
            DB::beginTransaction();
            $role->delete();
            DB::commit();
            return $this->sendResponse('Success', 'Role deleted successfully');
        } catch (Exception $error) {
            DB::rollBack();
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
