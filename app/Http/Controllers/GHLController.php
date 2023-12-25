<?php

namespace App\Http\Controllers;

use App\Services\GHLService;
use Illuminate\Http\Request;

class GHLController extends Controller
{
    protected $ghlService;

    public function __construct(GHLService $ghlService){
        $this->ghlService = $ghlService;
    }

    public function getUserWithOwnerRole($email){
        return $this->ghlService->getUserWithOwnerRole($email);
    }

    public function getLocation($ghl_location_id){
        return $this->ghlService->getGhlLocation($ghl_location_id);
    }

    public function updateUser($user_id, $body){
        return $this->ghlService->updateUser($user_id, $body);
    }

    public function createContact($email, $password){
        return $this->ghlService->createContact($email, $password);
    }

    public function createTask($contact, $ticket){
        return $this->ghlService->createTask($contact, $ticket);
    }

    public function redirectToGHL()
    {
        $url = 'https://marketplace.gohighlevel.com/oauth/chooselocation?response_type=code&redirect_uri='
                .env('GO_HIGH_LEVEL_REDIRECT').
                '&client_id='.env('GO_HIGH_LEVEL_CLIENT_ID').
                '&scope=businesses.readonly calendars.readonly calendars.write campaigns.readonly '.
                'conversations.readonly contacts.readonly contacts.write locations.readonly '.
                'locations/customValues.readonly locations/customFields.readonly locations/tasks.readonly '.
                'locations/tags.readonly opportunities.readonly opportunities.write users.readonly '.
                'calendars/events.readonly conversations/message.readonly conversations.write conversations/message.write';

        return redirect()->away($url);
    }

}