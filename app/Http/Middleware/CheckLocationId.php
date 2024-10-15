<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class CheckLocationId
{
    public function handle($request, Closure $next)    {
        $requestLocationId = $request->header('Locationid');
        Log::info('Location ID: ' . $requestLocationId);

        if (!$requestLocationId) {
            $response = [
                'success' => false,
                'message' => 'Location Id not provided in header',
            ];
            return response()->json($response, 400);
        }

        $location = Location::where('id', $requestLocationId)->first();

        if (!$location) {
            Log::info("No Location Found");
            $response = [
                'success' => false,
                'message' => 'Location Id not found',
            ];
            return response()->json($response, 400);
        }

        // Pass the $locationId variable to the request
        $request->merge(['location' => $location]);

        return $next($request);
    }
}
