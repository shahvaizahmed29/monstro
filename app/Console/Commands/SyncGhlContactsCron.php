<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Location;
use App\Http\Controllers\Api\Vendor\MemberController;

class SyncGhlContactsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:ghl-contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdXRoQ2xhc3MiOiJDb21wYW55IiwiYXV0aENsYXNzSWQiOiJaNDl3bWY4ZzM4UXN4ZWRvQmRpRyIsInNvdXJjZSI6IklOVEVHUkFUSU9OIiwic291cmNlSWQiOiI2NTc4Mzk0NTA4MWEzZTEyZjZlMGI0MTEtbHEyOThxa2YiLCJjaGFubmVsIjoiT0FVVEgiLCJwcmltYXJ5QXV0aENsYXNzSWQiOiJaNDl3bWY4ZzM4UXN4ZWRvQmRpRyIsIm9hdXRoTWV0YSI6eyJzY29wZXMiOlsiYnVzaW5lc3Nlcy5yZWFkb25seSIsImJ1c2luZXNzZXMud3JpdGUiLCJjb250YWN0cy5yZWFkb25seSIsImNvbnRhY3RzLndyaXRlIiwibG9jYXRpb25zLndyaXRlIiwibG9jYXRpb25zLnJlYWRvbmx5IiwibG9jYXRpb25zL2N1c3RvbVZhbHVlcy53cml0ZSIsImxvY2F0aW9ucy9jdXN0b21WYWx1ZXMucmVhZG9ubHkiLCJsb2NhdGlvbnMvY3VzdG9tRmllbGRzLnJlYWRvbmx5IiwibG9jYXRpb25zL2N1c3RvbUZpZWxkcy53cml0ZSIsImxvY2F0aW9ucy90YWdzLnJlYWRvbmx5IiwibG9jYXRpb25zL3RhZ3Mud3JpdGUiLCJvcHBvcnR1bml0aWVzLnJlYWRvbmx5Iiwib3Bwb3J0dW5pdGllcy53cml0ZSIsIm9hdXRoLnJlYWRvbmx5Iiwib2F1dGgud3JpdGUiXSwiY2xpZW50IjoiNjU3ODM5NDUwODFhM2UxMmY2ZTBiNDExIiwiY2xpZW50S2V5IjoiNjU3ODM5NDUwODFhM2UxMmY2ZTBiNDExLWxxMjk4cWtmIn0sImlhdCI6MTcwMjM5MDg0My45ODEsImV4cCI6MTcwMjQ3NzI0My45ODF9.g091fh2ivrXGwuoxjp7kiT7PlUWUT82SjLmS5LriJhU';
        $companyId = 'Z49wmf8g38QsxedoBdiG';
        // $locations = Location::all();
        $locations = Location::where('go_high_level_location_id', 'kxsCgZcTUell5zwFkTUc')->get();
        foreach($locations as $location) {
            try {
                $tokenObj = Http::withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'Version' => '2021-07-28'                
                ])->asForm()->post('https://services.leadconnectorhq.com/oauth/locationToken', [
                    'companyId' => $companyId,
                    'locationId' => $location->go_high_level_location_id,
                ]);
        
                if ($tokenObj->failed()) {
                    Log::info('=== SyncGhlContactsCron === (Location Token) =>'.json_encode($tokenObj->json()));
                }
                
                $url = 'https://services.leadconnectorhq.com/contacts/?locationId='.$location->go_high_level_location_id.'&limit=100';
    
                $tokenObj = $tokenObj->json();
              
                do {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '.$tokenObj['access_token'],
                        'Version' => '2021-07-28'
                    ])->get($url);
        
                    if ($response->failed()) {
                        $response->throw();    
                    }
                    $response = $response->json();
                    $contacts = $response['contacts'];
                    $url = null;
                    if(isset($response['meta'])) {
                        if(isset($response['meta']['nextPageUrl'])) {
                            $url = $response['meta']['nextPageUrl'];
                            $url = str_replace('http://', 'https://', $url);
                        }
                    }
                    foreach($contacts as $contact) {
                        MemberController::createMemberFromGHL($contact, $location);
                    }
                } while($url);
    
            } catch(\Exception $error) {
                Log::info($error->getMessage());
            }
        }
    }
}
