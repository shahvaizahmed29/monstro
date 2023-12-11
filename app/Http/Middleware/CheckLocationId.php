<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Location;

class CheckLocationId
{
    public function handle($request, Closure $next)
    {
        $requestLocationId = $request->header('Locationid');

        if (!$requestLocationId) {
            return response()->json(['error' => 'Location Id not provided'], 400);
        }

        $location = Location::where('go_high_level_location_id', $requestLocationId)->first();

        if (!$location) {
            return response()->json(['error' => 'Location Id not found'], 400);
        }

        // Pass the $locationId variable to the request
        $request->merge(['location' => $location]);
    
        return $next($request);
    }
}
