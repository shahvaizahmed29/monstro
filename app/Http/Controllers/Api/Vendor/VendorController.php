<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GHLController;
use App\Models\Location;
use App\Models\Vendor;
use Exception;
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
           
            $ghl_user_response = $this->ghl_controller->getUserWithTypeAndRole($user->email,'account','admin');
            
            if(count($ghl_user_response['users']) == 0 || !isset($ghl_user_response['users'])) {
                return $this->sendError('Error getting users from ghl. Please email support help@mymonstro.com', [], 400);
            }

            $ghl_user = $ghl_user_response['users'][0];

            if ($ghl_user && isset($ghl_user['email'])) {
                $ghl_location_id = $ghl_user['roles']['locationIds'][0];
                if ($ghl_location_id) {
                    $ghl_location_data = $this->ghl_controller->getLocation($ghl_location_id);
                    $ghl_location_data = $ghl_location_data['location'];
                    Location::updateOrCreate([
                        'go_high_level_location_id' => $ghl_location_id
                    ],
                    [
                        'go_high_level_location_id' => $ghl_location_data['id'],
                        'name' => $ghl_location_data['name'],
                        'address' => isset($ghl_location_data['address']) ? $ghl_location_data['address'] : null,
                        'city' => isset($ghl_location_data['city']) ? $ghl_location_data['city'] : $ghl_location_data['city'],
                        'state' => isset($ghl_location_data['state']) ? $ghl_location_data['state'] : null,
                        'logo_url' => isset($ghl_location_data['logoUrl']) ? $ghl_location_data['logoUrl'] : null,
                        'country' => isset($ghl_location_data['country']) ? $ghl_location_data['country'] : null,
                        'postal_code' => isset($ghl_location_data['postalCode']) ? $ghl_location_data['postalCode'] : null,
                        'website' => isset($ghl_location_data['website']) ? $ghl_location_data['website'] : null,
                        'email' => $ghl_location_data['email'],
                        'phone' => isset($ghl_location_data['phone']) ? $ghl_location_data['phone'] : null,
                        'vendor_id' => $vendor->id,
                        'meta_data' => $ghl_location_data
                    ]);
                }

                $vendor->company_name = $ghl_location_data['name'];
                $vendor->company_email = $ghl_location_data['email'];
                $vendor->company_website = isset($ghl_location_data['website']) ? $ghl_location_data['website'] : null;
                $vendor->company_address = isset($ghl_location_data['address']) ? $ghl_location_data['address'] : null;
                $vendor->go_high_level_user_id = $ghl_user['id'];
                $vendor->save();

                $this->ghl_controller->updateUser($ghl_user['id'], [
                    'email' => $vendor->email,
                    'password' => $new_password
                ]);
                
                $updateContact = [
                    'locationId' => 'kxsCgZcTUell5zwFkTUc', //Main Location To Manage All Users
                    'email' => $vendor->company_email,
                    'customFields' => [[
                        'key' => 'password',
                        'field_value' => $new_password
                    ]],
                ];
                $this->ghl_controller->upsertContact($updateContact);
                return $this->sendResponse('Success', 'Password set successfully');
            }else{
                return $this->sendError('Error setting contact up your password. Please email support help@mymonstro.com', [], 400);
            }
        } catch (\Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }
}