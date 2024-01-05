<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends BaseController
{
    public function profileUpdate(Request $request, $userId){
        try{
            $user = User::find($userId);

            if(!$user){
                return $this->sendError('User not exist.', [], 400);
            }

            $user->name = $request->name;
            $user->save();
            return $this->sendResponse('Success', 'User updated successfully.');
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function imageUpdate(Request $request, $userId){
        try{
            $img = $request->file('image');
            $imgPath = 'user-images/';
            $user = User::find($userId);

            if(!$user){
                return $this->sendError('User not exist.', [], 400);
            }

            $uploadedFileName = app('uploadImage')($userId, $img, $imgPath);
            $user->member->avatar = $uploadedFileName;
            $user->member->save();

            return $this->sendResponse('Success', 'Image updated successfully.');
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function updatePassword(Request $request, $id){
        try {
            $user = User::find($id);;
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $new_password = $request->input('password');
            $user->password = $new_password;
            $user->save();

            return $this->sendResponse('Success', 'Password set successfully');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
