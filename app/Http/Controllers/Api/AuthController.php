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
        $user = User::with(['vendor', 'member', 'staff.location', 'roles.permissions'])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'These credentials do not match our records.',
            ], 401);
        }

        $role = $user->roles
        ->filter(fn($role) => !in_array($role->id, [1, 2, 3, 4])) // Exclude roles with id 1 to 4
        ->first();

        $token = $user->createToken('<monstro@2023!/>')->plainTextToken;
        if ($user->hasRole(\App\Models\User::VENDOR)) {
            $locations = $this->getEncryptedLocations($user->vendor->locations);
            Log::info(json_encode($locations));
            $user = [
                'id' => $user->id,
                'email' => $user->email,
                // 'secondary_email' => $user->vendor->company_email,
                'firstName' => $user->vendor->first_name,
                'lastName' => $user->vendor->last_name,
                'name' => $user->name,
                'stripeCustomerId' => $user->vendor->stripe_customer_id,
                'phone' => $user->vendor->phone_number,
                'avatar' => $user->vendor->logo,
                'locations' => $locations,
                'vendor' => $user->vendor,
                'role' => $role,
                'member' => false,
            ];
        } else if ($user->hasRole(\App\Models\User::STAFF)) {
            Log::info(json_encode($user->staff->location->vendor));
            $locations = $this->getEncryptedLocations(collect([$user->staff->location]));
            Log::info(json_encode($locations));
            $user = [
                'id' => $user->id,
                'email' => $user->email,
                // 'secondary_email' => $user->vendor->company_email,
                'name' => $user->name,
                'firstName' => $user->staff->first_name,
                'lastName' => $user->staff->last_name,
                'stripeCustomerId' => $user->staff->location->vendor->stripe_customer_id,
                'phone' => $user->staff->phone_number,
                'avatar' => $user->staff->logo,
                'locations' => $locations,
                'staff' => $user->staff,
                'role' => $role,
                'vendor' => false,
            ];
        } else {
            $locations = $this->getEncryptedLocations($user->member->locations);
            $user = [
                'id' => $user->id,
                'email' => $user->email,
                // 'secondary_email' => $user->member->email,
                'firstName' => $user->member->first_name,
                'lastName' => $user->member->last_name,
                'name' => $user->member->first_name.' '.$user->member->last_name,
                'phone' => $user->member->phone,
                'referralCode' => $user->member->referral_code,
                'avatar' => $user->member->avatar,
                'locations' => $locations,
                'member' => $user->member,
                'role' => $role,
                'vendor' => false,
            ];
        }

        $user['token'] = $token;

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
