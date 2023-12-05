<?php

namespace App\Http\Controllers\Api\Vendor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Vendor;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Vendor\MemberController;

class PublicController extends BaseController
{
    
    public function syncMembersByLocation($locationId) {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdXRoQ2xhc3MiOiJDb21wYW55IiwiYXV0aENsYXNzSWQiOiJHOXVDeTk3bVB2NGl2RFF4OHoyMCIsInNvdXJjZSI6IklOVEVHUkFUSU9OIiwic291cmNlSWQiOiI2NGQyMjhmY2VhOTA0YjFkODQwMTFlNDctbG41MG54MWUiLCJjaGFubmVsIjoiT0FVVEgiLCJwcmltYXJ5QXV0aENsYXNzSWQiOiJHOXVDeTk3bVB2NGl2RFF4OHoyMCIsIm9hdXRoTWV0YSI6eyJzY29wZXMiOlsiYnVzaW5lc3Nlcy5yZWFkb25seSIsImNhbGVuZGFycy5yZWFkb25seSIsImNhbGVuZGFycy53cml0ZSIsImNhbXBhaWducy5yZWFkb25seSIsImNvbnZlcnNhdGlvbnMucmVhZG9ubHkiLCJjb250YWN0cy5yZWFkb25seSIsImNvbnRhY3RzLndyaXRlIiwibG9jYXRpb25zLnJlYWRvbmx5IiwibG9jYXRpb25zL2N1c3RvbVZhbHVlcy5yZWFkb25seSIsImxvY2F0aW9ucy9jdXN0b21GaWVsZHMucmVhZG9ubHkiLCJsb2NhdGlvbnMvdGFza3MucmVhZG9ubHkiLCJsb2NhdGlvbnMvdGFncy5yZWFkb25seSIsIm9wcG9ydHVuaXRpZXMucmVhZG9ubHkiLCJvcHBvcnR1bml0aWVzLndyaXRlIiwidXNlcnMucmVhZG9ubHkiLCJjYWxlbmRhcnMvZXZlbnRzLnJlYWRvbmx5IiwiY29udmVyc2F0aW9ucy9tZXNzYWdlLnJlYWRvbmx5Iiwib2F1dGgud3JpdGUiLCJvYXV0aC5yZWFkb25seSJdLCJjbGllbnQiOiI2NGQyMjhmY2VhOTA0YjFkODQwMTFlNDciLCJjbGllbnRLZXkiOiI2NGQyMjhmY2VhOTA0YjFkODQwMTFlNDctbG41MG54MWUifSwiaWF0IjoxNzAxNzcxNDM1LjQ2NCwiZXhwIjoxNzAxODU3ODM1LjQ2NH0.56Uo96nfZvaivTu1Id2Cp572QgUbhKJs4Unk299UhBw';
        $companyId = 'G9uCy97mPv4ivDQx8z20';
        try {
            $tokenObj = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Version' => '2021-07-28'                
            ])->asForm()->post('https://services.leadconnectorhq.com/oauth/locationToken', [
                'companyId' => $companyId,
                'locationId' => $locationId,
            ]);
    
            if ($tokenObj->failed()) {
                return $this->sendError('Something went wrong!', json_encode($tokenObj->json()));
            }
            
            $url = 'https://services.leadconnectorhq.com/contacts/?locationId='.$locationId.'&limit=100';

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
                    MemberController::createMemberFromGHL($contact);
                }
                \Log::info($url);
                \Log::info(count($contacts));
                \Log::info(json_encode($response['meta']));
            } while($url);

        } catch(\Exception $error) {
            return $this->sendError('Something went wrong!', $error->getMessage());
        }
        return $this->sendResponse([], 'Members synced successfully');
    }

}
