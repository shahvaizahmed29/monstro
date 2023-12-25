<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\LocationResource;
use App\Models\Location;

class LocationsController extends BaseController
{
    public function vendorLocations($vendor_id){
        $locations = Location::where('vendor_id', $vendor_id)->paginate(25);
        $data = [
            'locations' => LocationResource::collection($locations),
            'pagination' => [
                'current_page' => $locations->currentPage(),
                'per_page' => $locations->perPage(),
                'total' => $locations->total(),
                'prev_page_url' => $locations->previousPageUrl(),
                'next_page_url' => $locations->nextPageUrl(),
                'first_page_url' => $locations->url(1),
                'last_page_url' => $locations->url($locations->lastPage()),
            ],
        ];

        return $this->sendResponse($data, 'Vendor locations.');
    }

}