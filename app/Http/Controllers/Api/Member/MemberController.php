<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends BaseController
{
    public function profileUpdate(Request $request){

    }

    public function imgUpdate(Request $request, $user_id){
        try{
            $img = $request->file('image');
            $imgPath = 'user-images/';

            $uploadedImage = app('uploadImage')($user_id, $img, $imgPath);
            $user = User::find($user_id);
            
            if(!$user){
                
            }
        }catch (Exception $error) {
            Log::info('===== MemberController - imgUpdate() - error =====');
            Log::info($error->getMessage());
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
