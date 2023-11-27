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
        $user= User::with('member')->where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['These credentials do not match our records.']
            ], 404);
        }
    
        $token = $user->createToken('<monstro@2023!/>')->plainTextToken;
        
        $user = [
            'id' => $user->id,
            'email' => $user->email,
            'secondary_email' => $user->member->email,
            'name' => $user->member->name,
            'phone' => $user->member->phone,
            'referral_code' => $user->member->referral_code,
            'avatar' => $user->member->avatar,
        ];
        
        $response = [
            'user' => $user,
            'token' => $token
        ];
    
        return $this->sendResponse($response, 'Success');
    }

}