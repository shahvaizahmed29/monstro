<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\SycnGHLLocations;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Setting;
use Exception;
use DB;

class PublicController extends BaseController
{
    public function redirectToGHL()
    {
        $url = 'https://marketplace.gohighlevel.com/oauth/chooselocation?response_type=code&redirect_uri='
                .env('GO_HIGH_LEVEL_REDIRECT').
                '&client_id='.env('GO_HIGH_LEVEL_CLIENT_ID').
                '&scope=businesses.readonly businesses.write contacts.readonly contacts.write locations.write locations.readonly '.
                'locations/customValues.write locations/customValues.readonly locations/customFields.readonly locations/customFields.write '.
                'locations/tags.readonly locations/tags.write opportunities.readonly '.
                'opportunities.write oauth.readonly users.readonly users.write';
        return redirect()->away($url);
    }

    public function storeGHL(Request $request){
        $sourceId = session('sourceId');

        $code = $request->code;

        $response = Http::asForm()->post('https://services.leadconnectorhq.com/oauth/token', [
            'client_id' => env('GO_HIGH_LEVEL_CLIENT_ID'),
            'client_secret' => env('GO_HIGH_LEVEL_SECRET'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => env('GO_HIGH_LEVEL_REDIRECT'),
            'user_type' => 'Company'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Setting::updateOrCreate([
                'name' => 'ghl_integration'
            ],
            [
                'name' => 'ghl_integration',
                'value' => $data['access_token'],
                'meta_data' => $data
            ]);
            dd('Successfully Added');
           
        } else {
            Log::info("==== Error in getting the Go High Level response=====");
            Log::info($response->json());
        }
    }

    public function imageUpdate(ImageUploadRequest $request, $userId){
        try{
            $img = $request->file('image');
            $imgPath = 'user-images/';
            $user = User::find($userId);

            if(!$user){
                return $this->sendError('User not exist.', [], 400);
            }

            $uploadedFileName = app('uploadImage')($userId, $img, $imgPath);

            if($user->hasRole(\App\Models\User::VENDOR)) {
                $user->vendor->logo = $uploadedFileName;
            }else{
                $user->member->avatar = $uploadedFileName;
            }
            
            $user->member->save();
            return $this->sendResponse('Success', 'Image updated successfully.');
        }catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
