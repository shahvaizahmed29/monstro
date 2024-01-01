<?php

namespace App\Services;

use App\Enums\TicketStatus;
use Exception;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GHLService
{

    protected $ghlIntegration;

    public function __construct(){
        $ghlIntegration = Setting::where('name', 'ghl_integration')->first();
        $this->ghlIntegration = $ghlIntegration;
    }

    public function getUserWithOwnerRole($email){
      

        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->ghlIntegration['value'],
            'Version' => config('services.ghl.api_version'),
        ])->get(config('services.ghl.api_url') . `users/search?query=` .$email);
        
        return $response->json();
    }

    
    public function getGhlLocation($location_id){
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->ghlIntegration['value'],
            'Version' => config('services.ghl.api_version'),
        ])->get(config('services.ghl.api_url') .`locations/{$location_id}`);

        if ($response->successful()) {
            $ghl_location_data = $response->json();
            return $ghl_location_data;
        } else {
            Log::info('==== GHL SERVICE - getGhlLocation() =====');
            Log::info(json_encode($response->json()));
            return null;
        }
    }


    public function updateUser($user_id, $body){
        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->ghlIntegration['value'],
            'Version' => config('services.ghl.api_version'),
        ])->put(config('services.ghl.api_url') .`users/{$user_id}`, $body);
        
        if ($response->successful()) {
            return $response->json();
        } else {
            Log::info('==== GHL SERVICE - updateUser() =====');
            Log::info(json_encode($response->json()));
            return null;
        }
    }


    public function createContact($data){
        $locationObj = $this->generateLocationLevelKey($data['locationId']);
        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $locationObj['access_token'],
            'Version' => config('services.ghl.api_version'),
        ])->post(config('services.ghl.api_url') .'contacts', $data);
        
        if ($response->successful()) {
            return $response->json();
        } else {
            Log::info('==== GHL SERVICE - createContact() =====');
            Log::info(json_encode($response->json()));
            Log::info($locationObj['access_token']);
            Log::info(json_encode($data));
            Log::info(config('services.ghl.api_url') .'contacts/');
            return null;
        }
    }


    public function createTask($contact, $ticket){
        $response =  Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->ghlIntegration['value'],
            'Version' => config('services.ghl.api_version'),
        ])->post(config('services.ghl.api_url') .`contacts/`, [
            'name' => $contact['name'],
            'email' => $contact['email'],
            'customField' => [
                'ticket_number' => $ticket['id'],
                'account' => $ticket['accountId'],
                'subject' => $ticket['subject'],
                'issue' => $ticket['issue'],
                'video' => $ticket['video'] ?? null,
                'description' => $ticket['description'] ?? null,
                'status' => TicketStatus::OPEN,
            ],
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            Log::info('==== GHL SERVICE - createTask() =====');
            Log::info(json_encode($response->json()));
            return null;
        }
    }

    public function updateContact($data, $contactId){
        try {
            $response = Http::withHeaders([
                'Content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->ghlIntegration['value'],
                'Version' => config('services.ghl.api_version'),
            ])->put(config('services.ghl.api_url') .`contacts/$contactId`, $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::info('==== GHL SERVICE - updateContact() =====');
                Log::info(json_encode($response->json()));
                return null;
            }
        } catch (Exception $error) {
           return $error->getMessage();
        }
    }

    public function generateLocationLevelKey($location_id) {
        $tokenObj = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->ghlIntegration['value'],
            'Version' => '2021-07-28'                
        ])->asForm()->post('https://services.leadconnectorhq.com/oauth/locationToken', [
            'companyId' => $this->ghlIntegration['meta_data']['companyId'],
            'locationId' => $location_id,
        ]);

        if ($tokenObj->failed()) {
            $tokenObj->throw();
        }
        
        $url = 'https://services.leadconnectorhq.com/contacts/?locationId='.$location_id.'&limit=100';

        return $tokenObj->json();
    }

}