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
        $user = User::with(['vendor', 'member', 'roles'])->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'These credentials do not match our records.',
            ], 401);
        }

        $token = $user->createToken('<monstro@2023!/>')->plainTextToken;
        if ($user->hasRole(\App\Models\User::VENDOR)) {
            $locations = $this->getEncryptedLocations($user->vendor->locations);
            Log::info(json_encode($locations));
            $user = [
                'id' => $user->id,
                'email' => $user->email,
                // 'secondary_email' => $user->vendor->company_email,
                'name' => $user->name,
                'stripeCustomerId' => $user->vendor->stripe_customer_id,
                'phone' => $user->vendor->phone_number,
                'avatar' => $user->vendor->logo,
                'locations' => $locations,
                'vendor' => $user->vendor,
                'member' => false,
            ];
        } else {
            $locations = $this->getEncryptedLocations($user->member->locations);
            $user = [
                'id' => $user->id,
                'email' => $user->email,
                // 'secondary_email' => $user->member->email,
                'name' => $user->member->first_name.' '.$user->member->last_name,
                'phone' => $user->member->phone,
                'referralCode' => $user->member->referral_code,
                'avatar' => $user->member->avatar,
                'locations' => $locations,
                'member' => $user->member,
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
