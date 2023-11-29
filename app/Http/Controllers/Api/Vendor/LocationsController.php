<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\LocationResource;

class LocationsController extends BaseController
{
    public function getVendorLocations(){
        return $this->sendResponse(LocationResource::collection(auth()->user()->vendor->locations), 'Vendor locations');
    }

}
