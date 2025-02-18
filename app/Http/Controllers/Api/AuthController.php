<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Hashids\Hashids;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Sqids\Sqids;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        Log::info("User email: " . $request->email);
        $user = User::with(['vendor', 'member', 'staff.location', 'roles.permissions'])->where('email', $request->email)->first();
        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     return response([
        //         'message' => 'These credentials do not match our records.',
        //     ], 401);
        // }

        $role = $user->roles
        ->filter(fn($role) => !in_array($role->id, [1, 2, 3, 4])) // Exclude roles with id 1 to 4
        ->first();

        $token = $user->createToken('<monstro@2023!/>')->plainTextToken;
        $user = [
            'role' => $role,
            'token' => $token
        ];


        return $this->sendResponse($user, 'Success');
    }

    function getEncryptedLocations($locations)
    {
        return $locations->map(function ($location) {
            $locationArray = $location->toArray(); // Convert to array
            $sqids = new Sqids('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 14);
            $locationArray['id'] = $sqids->encode([$locationArray['id']]);
            return (object) $locationArray; // Convert back to an object if needed
        });
    }

}
