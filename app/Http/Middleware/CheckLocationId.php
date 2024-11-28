<?php

namespace App\Http\Middleware;

use App\Models\Location;
use Closure;
use Illuminate\Support\Facades\Log;
// use Hashids\Hashids;
use Sqids\Sqids;

class CheckLocationId
{
    public function handle($request, Closure $next)
    {
        $requestLocationId = $request->header('Locationid');
        Log::info('Location ID: ' . $requestLocationId);

        if (!$requestLocationId) {
            $response = [
                'success' => false,
                'message' => 'Location Id not provided in header',
            ];
            return response()->json($response, 400);
        }
        // $sqids = new Sqids('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 14);
        // $locationId = $sqids->decode($requestLocationId);
        // Log::info(json_encode($locationId));
        // $locationId = $locationId ? $locationId[0] : null;
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

    public function decodeLocationId($encodedId)
    {
        $sqids = new Sqids('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 14);
        $decoded = $sqids->decode($encodedId);
        return $decoded ? $decoded[0] : null;
    }
}
