<?php

namespace App\Services;

use App\Enums\TicketStatus;
use Illuminate\Support\Facades\Http;

class GHLService
{
    public function getUserWithOwnerRole($email){
        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . config('services.ghl.agency_key'),
            'Version' => config('services.ghl.api_version'),
        ])->get(config('services.ghl.api_url') . `users/search?query=` .$email);
        
        return $response->json();
    }

    
    public function getGhlLocation($location_id){
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.ghl.agency_key'),
            'Version' => config('services.ghl.api_version'),
        ])->get(config('services.ghl.api_url') .`locations/{$location_id}`);

        if ($response->successful()) {
            $ghl_location_data = $response->json();
            return $ghl_location_data;
        } else {
            \Log::info('==== GHL SERVICE - getGhlLocation() =====');
            \Log::info(json_encode($response->json()));
            return null;
        }
    }


    public function updateUser($user_id, $body){
        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . config('services.ghl.agency_key'),
            'Version' => config('services.ghl.api_version'),
        ])->put(config('services.ghl.api_url') .`users/{$user_id}`, $body);
        
        if ($response->successful()) {
            return $response->json();
        } else {
            \Log::info('==== GHL SERVICE - updateUser() =====');
            \Log::info(json_encode($response->json()));
            return null;
        }
    }


    public function createContact($email, $password){
        $response = Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . config('services.ghl.agency_key'),
            'Version' => config('services.ghl.api_version'),
        ])->post(config('services.ghl.api_url') .`contacts`, [
            'email' => $email,
            'customField' => [
                'password' => $password
            ],
        ]);
        
        if ($response->successful()) {
            return $response->json();
        } else {
            \Log::info('==== GHL SERVICE - createContact() =====');
            \Log::info(json_encode($response->json()));
            return null;
        }
    }


    public function createTask($contact, $ticket){
        $response =  Http::withHeaders([
            'Content-type' => 'application/json',
            'Authorization' => 'Bearer ' . config('services.ghl.agency_key'),
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
            \Log::info('==== GHL SERVICE - createTask() =====');
            \Log::info(json_encode($response->json()));
            return null;
        }
    }


}