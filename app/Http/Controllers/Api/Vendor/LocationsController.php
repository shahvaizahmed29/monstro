<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\Request;

class LocationsController extends BaseController
{
    public function getVendorLocations(){
        $vendor_locations = Vendor::with(['locations'])->where('user_id', 1)->first();
        return $this->sendResponse(new VendorResource($vendor_locations), 'Get Vendor related locations');
    }

}
