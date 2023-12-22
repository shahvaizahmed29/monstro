<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GHLController;
use App\Models\Location;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VendorController extends BaseController
{
    protected $ghl_controller;

    public function __construct(GHLController $ghl_controller)
    {
        $this->ghl_controller = $ghl_controller;
    }

    public function updatePassword(Request $request, $id)
    {
        try {
            $vendor = Vendor::find($id);
            $user = $vendor->user;
            if (!$user) {
                return response()->json(['message' => 'Vendor not found'], 404);
            }

            $new_password = $request->input('password');
            $hashed_password = Hash::make($new_password);
            $user->password = $hashed_password;
            $user->save();

            $ghl_user = $this->ghl_controller->getUserByEmail($user->email);

            if ($ghl_user && isset($ghl_user['email'])) {
                $ghl_location_id = $ghl_user['roles']['locationIds'][0];
                
                if ($ghl_location_id) {
                    $ghl_location_data = $this->ghl_controller->getLocation($ghl_location_id);
                    
                    Location::create([
                        'go_high_level_location_id' => $ghl_location_data['id'],
                        'name' => $ghl_location_data['name'],
                        'address' => $ghl_location_data['address'],
                        'city' => $ghl_location_data['city'],
                        'state' => $ghl_location_data['state'],
                        'logo_url' => $ghl_location_data['logoUrl'],
                        'country' => $ghl_location_data['country'],
                        'postal_code' => $ghl_location_data['postalCode'],
                        'website' => $ghl_location_data['website'],
                        'email' => $ghl_location_data['email'],
                        'phone' => $ghl_location_data['phone'],
                        'vendor_id' => $vendor->id,
                        'meta_data' => $ghl_location_data
                    ]);
                }

                $this->ghl_controller->updateUser($ghl_user['id'], [
                    'firstName' => $vendor->first_name,
                    'lastName' => $vendor->last_name,
                    'email' => $vendor->email,
                    'password' => $new_password,
                    'type' => 'account',
                    'role' => 'admin',
                ]);

                $this->ghl_controller->createContact($vendor->email, $new_password);
                return $this->sendResponse('Success', 'Password set successfully');
            }else{
                return $this->sendError('Error setting contact up your password. Please email support help@mymonstro.com', [], 400);
            }

        } catch (\Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}