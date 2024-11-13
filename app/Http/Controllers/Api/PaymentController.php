<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Vendor\VendorController;
use App\Http\Controllers\BaseController;
use App\Http\Requests\DepositRequest;
use App\Models\Integration;
use App\Models\Location;
use App\Models\Member;
use App\Models\Program;
use App\Models\StripePlan;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorProgress;
use App\Services\GHLService;
use App\Services\StripeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends BaseController
{
    protected $stripeService;
    protected $ghlService;
    protected $vendor_controller;

    public function __construct(StripeService $stripeService, GHLService $ghlService, VendorController $vendor_controller)
    {
        $this->stripeService = $stripeService;
        $this->ghlService = $ghlService;
        $this->vendor_controller = $vendor_controller;
    }

    public function deposit(DepositRequest $request)
    {
        try {
            $vendor = $request->input('owner');
            $token = $request->input('token');

            $customer = $this->stripeService->createCustomer($vendor, $token['id']);
            $this->stripeService->setupIntents($customer, $token);

            return $this->sendResponse('Success', 'Stripe customer created successfully.');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function subscribe(Request $request)
    {
        try {
            $vendorInput = $request->input('owner');
            $coupon = $request->input('coupon');
            $plan = $request->input('plan');
            $token = $request->input('token');

            $setupFee = $plan['setup'] * 100;
            $planName = strtolower($plan['name']);

            if ($coupon && in_array($planName, $coupon['plans']) && $coupon['planCycle'] === $plan['cycle']) {
                $setupFee *= ($coupon['discount'] / 100);
            }

            $setupFee = ($setupFee === 0) ? 100 : $setupFee;

            $stripeCustomerId = null;
            $vendor = null;
            $user = User::with('vendor')->where('email', $vendorInput['email'])->first();

            DB::beginTransaction();
            if ($user) {
                $stripeCustomerId = $user->vendor->stripe_customer_id;
                $vendor = $user->vendor;
            } else {
                $stripeCustomer = $this->stripeService->createCustomer($vendorInput, $token['id']);
                $stripeCustomerId = $stripeCustomer['id'];
                $password = str_replace(' ', '', $vendorInput['firstName']) . '@' . Carbon::now()->year . '!';

                $user = User::create([
                    'name' => $vendorInput['firstName'] . ' ' . $vendorInput['lastName'],
                    'email' => $vendorInput['email'],
                    'password' => bcrypt($password),
                    'email_verified_at' => now(),
                ]);
                $user->assignRole(\App\Models\User::VENDOR);

                $vendor = Vendor::create([
                    'first_name' => $vendorInput['firstName'],
                    'last_name' => isset($vendorInput['lastName']) ? $vendorInput['lastName'] : null,
                    'user_id' => $user->id,
                    'company_name' => $vendorInput['firstName'] . ' ' . isset($vendorInput['lastName']) ? $vendorInput['lastName'] : null,
                    'company_email' => $vendorInput['email'],
                    'plan_id' => $plan['id'],
                    'phone_number' => $vendorInput['phone'],
                    'stripe_customer_id' => $stripeCustomerId,
                ]);
            }

            $paymentMethod = $this->stripeService->getPaymentMethods($stripeCustomerId);
            $this->stripeService->attachPaymentMethod($stripeCustomerId, $paymentMethod->data[0]->id);
            $clientSecret = $this->stripeService->createPaymentIntent($setupFee, $stripeCustomerId, $paymentMethod->data[0]->id);
            $subscriptionStatus = $this->stripeService->createSubscription($plan['name'], $plan['cycle'], $stripeCustomerId);

            if ($clientSecret && $subscriptionStatus) {
                $steps = [];
                for ($i = 1; $i <= 5; $i++) {
                    $steps[] = [
                        'vendor_id' => $vendor->id,
                        'progress_step_id' => $i,
                        'active' => ($i === 1),
                    ];
                }

                VendorProgress::insert($steps);

                $tags = ["new lead", "booked appointment", "customer"];
                $contactData = [
                    'locationId' => 'kxsCgZcTUell5zwFkTUc', //Main Location To Manage All Users
                    'email' => $vendor->company_email,
                    'firstName' => $vendor->first_name,
                    'lastName' => $vendor->last_name,
                    'tags' => $tags,
                    'customFields' => [[
                        'key' => 'plan_type',
                        'field_value' => $planName,
                    ], [
                        'key' => 'onboarding',
                        'field_value' => "https://join.mymonstro.com/onboarding/{$vendor->id}",
                    ]],
                ];
                $ghlContact = $this->ghlService->createContact($contactData);
                DB::commit();
                return $this->sendResponse($vendor->id, 'Subscription successfull.');
            } else {
                return $this->sendError('Payment declined.', [], 500);
            }
        } catch (Exception $error) {
            DB::rollBack();
            Log::info('===== PaymentController - subscribe() - error =====');
            Log::info($error->getMessage());
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function completeSubscription(Request $request)
    {
        $member = Auth::user()->member;
        $program = Program::with(['location'])->where('id', $request->programId)->first();
        $stripeDetails = Integration::where(['vendor_id' => $program->location->vendor_id, "service" => "Stripe"])->first();
        $stripe = new \Stripe\StripeClient(['api_key' => $stripeDetails->access_token]);
        $oldCustomer = $stripe->customers->search([
            'query' => 'email:\'' . $member['email'] . '\'',
        ]);
        try{
            if (count($oldCustomer['data'])) {
                $customer = $oldCustomer['data'][0];
            } else {
                $customer = $stripe->customers->create([
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'phone' => $member['phone'],
                ]);
                // Get the token from the request
                $token = $request->token;
    
                // Convert the token to a PaymentMethod
                $paymentMethod = $stripe->paymentMethods->create([
                    'type' => 'card',
                    'card' => ['token' => $token],
                ]);
    
                // Attach the PaymentMethod to the customer
                $stripe->paymentMethods->attach(
                    $paymentMethod->id,
                    ['customer' => $customer['id']]
                );
    
                // Set the attached PaymentMethod as the default payment method
                $stripe->customers->update($customer['id'], [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethod->id,
                    ],
                ]);
                $planId = request()->planId;
                $stripePlan = StripePlan::with('pricing')->find($planId);
                if($stripePlan->pricing->billing_period == "One Time"){
                    $payment = $stripe->paymentIntents->create([
                        'amount' => $stripePlan->pricing->amount,
                        'currency' => 'usd',
                        'automatic_payment_methods' => ['enabled' => true, 'allow_redirects' => 'never'],
                        'confirm' => true,
                        'customer' => $customer['id'],
                        'metadata' => [
                            'price' =>  $stripePlan->pricing->stripe_price_id
                        ],
                        'payment_method' => $paymentMethod->id
                      ]);
                      Log::info(json_encode($payment));
                      return $this->sendResponse($payment, 'Payment successfull.');
                } else {
                    $subscription = $stripe->subscriptions->create([
                        'customer' => $customer['id'],
                        'items' => [
                            ['price' => $stripePlan->pricing->stripe_price_id],
                        ],
                    ]);
                    return $this->sendResponse($subscription, 'Subscription successfull.');
                }
            }
        } catch(Exception $error) {
            DB::rollBack();
            Log::info('===== PaymentController - subscribe() - error =====');
            Log::info($error->getMessage());
            return $this->sendError($error->getMessage(), [], 500);
        }

    }

}
