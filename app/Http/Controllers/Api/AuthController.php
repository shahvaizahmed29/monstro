<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $user= User::with(['vendor','member','roles'])->where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'These credentials do not match our records.'
            ], 401);
        }
    
        $token = $user->createToken('<monstro@2023!/>')->plainTextToken;
        if($user->hasRole(\App\Models\User::VENDOR)) {
            $user = [
                'id' => $user->id,
                'email' => $user->email,
                'secondary_email' => $user->vendor->email,
                'name' => $user->vendor->name,
                'phone' => $user->vendor->phone,
                'referral_code' => $user->vendor->referral_code,
                'avatar' => $user->vendor->avatar,
                'locations' => $user->vendor->locations
            ];
        } else {
            $user = [
                'id' => $user->id,
                'email' => $user->email,
                'secondary_email' => $user->member->email,
                'name' => $user->member->name,
                'phone' => $user->member->phone,
                'referral_code' => $user->member->referral_code,
                'avatar' => $user->member->avatar,
            ];
        }
        
        $user['token'] = $token;
    
        return $this->sendResponse($user, 'Success');
    }

}