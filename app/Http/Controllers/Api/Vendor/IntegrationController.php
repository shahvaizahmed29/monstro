<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Models\Integration;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntegrationController extends BaseController
{
    public function getIntegrations()
    {
        $user = Auth::user();
        $vendorId = $user->vendor->id;
        try {
            $integrations = Integration::where('vendor_id', $vendorId)->get();
            return $this->sendResponse($integrations, 'Integration List');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function delIntegrations($id)
    {
        $integration = Integration::find($id);
        try {
            DB::beginTransaction();
            $integration->delete();
            DB::commit();
            return $this->sendResponse($integration, 'Integration Deleted');
        } catch (Exception $error) {
            DB::rollBack();
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function completeConnection(Request $request, $service)
    {
        $user = Auth::user();
        $vendorId = $user->vendor->id;
        $scope = $request->input("scope");
        $code = $request->input("code");
        $integration = Integration::where(['vendor_id' => $vendorId, "service" => $service])->first();
        if ($service == 'stripe') {
            $stripe = new StripeService();
            try {
                $token = $stripe->completeConnection($scope, $code);
                if ($token) {
                    DB::beginTransaction();
                    if($integration && $token->stripe_user_id == $integration->integration_id) {
                      $integration->update([
                        "api_key" => $token->stripe_publishable_key,
                        "secret_key" => $token->access_token,
                        "access_token" => $token->access_token,
                        "refresh_token" => $token->refresh_token,
                        "integration_id" => $token->stripe_user_id,
                        "additional_settings" => json_encode($token),
                      ]);
                    } else {
                      $integration = Integration::create([
                          "vendor_id" => $vendorId,
                          "service" => "Stripe",
                          "api_key" => $token->stripe_publishable_key,
                          "secret_key" => $token->access_token,
                          "access_token" => $token->access_token,
                          "refresh_token" => $token->refresh_token,
                          "integration_id" => $token->stripe_user_id,
                          "additional_settings" => json_encode($token),
                      ]);
                    }
                    DB::commit();
                    return $this->sendResponse($integration, 'Authorization Completed.');

                }
            } catch (Exception $error) {
                Log::error($error);
                return $this->sendError(null, $error->getMessage(), 500);
            }
        } else if ($service == 'ghl') {
          $response = Http::asForm()->post('https://services.leadconnectorhq.com/oauth/token', [
            'client_id' => env('GO_HIGH_LEVEL_CLIENT_ID'),
            'client_secret' => env('GO_HIGH_LEVEL_SECRET'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => env('GO_HIGH_LEVEL_REDIRECT'),
            'user_type' => 'Company'
        ]);
        } else {
            return $this->sendError(null, 'No Integration', 500);
        }
    }
}
