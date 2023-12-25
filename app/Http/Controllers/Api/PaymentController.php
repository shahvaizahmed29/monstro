<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\DepositRequest;
use App\Models\PaymentMethod;
use App\Models\Vendor;
use App\Models\VendorProgress;
use App\Services\GHLService;
use App\Services\StripeService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    protected $stripeService;
    protected $ghlService;

    public function __construct(StripeService $stripeService, GHLService $ghlService){
        $this->stripeService = $stripeService;
        $this->ghlService = $ghlService;
    }

    public function deposit(DepositRequest $request){
        try {
            $vendor = $request->input('owner');
            $token = $request->input('token');

            $customer = $this->stripeService->createCustomer($vendor, $token);
            $this->stripeService->setupIntents($customer, $token);

            return $this->sendResponse('Success', 'Stripe customer created successfully.');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function subscribe(Request $request){
        try {
            $vendor = $request->input('owner');
            $coupon = $request->input('coupon');
            $plan = $request->input('plan');
            $token = $request->input('token');

            $setupFee = $plan['setup'] * 100;
            $planName = strtolower($plan['name']);

            if ($coupon && in_array($planName, $coupon['plans']) && $coupon['planCycle'] === $plan['cycle']) {
                $setupFee *= ($coupon['discount'] / 100);
            }

            $setupFee = ($setupFee === 0) ? 100 : $setupFee;

            $customer = $this->stripeService->createCustomer($vendor, $token);
            $clientSecret = $this->stripeService->createPaymentIntent($setupFee, $customer['id'], $token['card']['id']);
            $subscriptionStatus = $this->stripeService->createSubscription($plan['name'], $plan['cycle'], $customer['id']);

            if ($clientSecret && $subscriptionStatus) {
                DB::beginTransaction();
                $newVendor = Vendor::create($vendor);
                PaymentMethod::create(['vendor_id' => $newVendor->id, 'stripe_customer_id' => $customer['id']]);

                $steps = [];
                for ($i = 1; $i <= 5; $i++) {
                    $steps[] = [
                        'vendor_id' => $newVendor->id,
                        'progressStepId' => $i,
                        'active' => ($i === 1),
                    ];
                }
                
                VendorProgress::insert($steps);
                DB::commit();

                $tags = ["new lead", "booked appointment", "customer"];
                $updates = [
                    'email' => $newVendor->email,
                    'tags' => $tags,
                    'customField' => [
                        'plan_type' => $planName,
                        'onboarding' => "https://join.mymonstro.com/onboarding/{$vendorId}",
                    ],
                ];

                $this->ghlService->updateContact($updates);
                return $this->sendResponse($newVendor, 'Subscription successfull.');
            } else {
                return $this->sendError('Payment declined.', [], 500);
            }
        } catch (Exception $error) {
            DB::rollBack();
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
