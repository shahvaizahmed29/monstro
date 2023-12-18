<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\SycnGHLLocations;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
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
                'opportunities.write oauth.readonly';
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
        ]);

        if ($response->successful()) {
            $data = $response->json();
            config()->set('GO_HIGH_LEVEL_AGENCY_KEY', $data['access_token']);
            dd($data);
        } else {
            Log::info("==== Error in getting the Go High Level response=====");
            Log::info($response->json());
        }
    }

    public function storeGhlLocation(Request $request){
        try {
            $checkIfLocationExist = Location::where('go_high_level_location_id', $request->locationId)->first();
            
            if($checkIfLocationExist) {
                return $this->sendError("Provided Location already exist", [], 400);
            }

            DB::beginTransaction();
            $user = User::where('email', $request->locationEmail)->first();
            
            if(!$user) {
                $user = User::create([
                    'name' => isset($request->locationName) ? $request->locationName: $request->locationId,
                    'email' =>  $request->locationEmail,
                    'password' => bcrypt(str_replace(' ', '', $request->locationLastName).'@2024!'),
                    'email_verified_at' => now()
                ]);
                $user->assignRole(\App\Models\User::VENDOR);

                $vendor = Vendor::create([
                    'user_id' => $user->id,
                    'company_name' => isset($request->locationName) ? $request->locationName : $request->locationId,
                    'company_email' =>  $request->locationEmail,
                    'company_website' => isset( $request->locationWebsite) ?  $request->locationWebsite : '',
                    'company_address' => isset($request->locationAddress) ? $request->locationAddress : ''
                ]);
            }

            $vendor = $user->vendor;
            $location = Location::create([
                'go_high_level_location_id' => $request->locationId,
                'name' => isset($request->locationName) ? $request->locationName : $request->locationId,
                'address' => isset($request->locationAddress) ? $request->locationAddress : '',
                'city' => isset($request->locationCity) ? $request->locationCity : '',
                'state' => isset($request->locationState) ? $request->locationState : '',
                'logo_url' => null,
                'country' => isset($request->locationCountry) ? $request->locationCountry : '',
                'postal_code' => isset($request->locationPostalCode) ? $request->locationPostalCode : '',
                'website' => isset($request->locationWebsite) ? $request->locationWebsite : '',
                'email' => $request->locationEmail,
                'phone' => isset($request->locationPhone) ? $request->locationPhone : '',
                'vendor_id' => $vendor->id,
                'meta_data' => $request->all()
            ]);
            DB::commit();

            return $this->sendResponse("Success", 'Vendor with location created.');
        } catch(Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
