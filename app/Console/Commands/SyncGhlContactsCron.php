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
        $ghl_integration = Setting::where('name', 'ghl_integration')->first();

        $token = $ghl_integration['value'];
        $companyId = $ghl_integration['companyId'];
        $locations = Location::all();
        // $locations = Location::where('go_high_level_location_id', 'kxsCgZcTUell5zwFkTUc')->get();
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
