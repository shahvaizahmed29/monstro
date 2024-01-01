<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Vendor\VendorController;
use App\Http\Controllers\BaseController;
use App\Http\Requests\DepositRequest;
use App\Http\Resources\Vendor\VendorResource;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\VendorProgress;
use App\Services\GHLService;
use App\Services\StripeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    protected $stripeService;
    protected $ghlService;
    protected $vendor_controller;

    public function __construct(StripeService $stripeService, GHLService $ghlService, VendorController $vendor_controller){
        $this->stripeService = $stripeService;
        $this->ghlService = $ghlService;
        $this->vendor_controller = $vendor_controller;
    }

    public function deposit(DepositRequest $request){
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
            
            DB::beginTransaction();
            $user = User::where('email', $vendor['email'])->first();

            if($user){
                $stripe_customer_id = $user->vendor->stripe_customer_id;
                $customerId = null;

                if(!$stripe_customer_id){
                    $customer = $this->stripeService->createCustomer($vendor, $token['id']);
                    $customerId = $customer['id'];
                }else{
                    $customerId = $user->vendor->stripe_customer_id;
                }
            }else{
                $customer = $this->stripeService->createCustomer($vendor, $token['id']);
                $customerId = $customer['id'];
                $user = $this->createUser($vendor);
                $newVendor = $this->vendor_controller->createVendor($user, $vendor, $plan);
            }

            $paymentMethod = $this->stripeService->getPaymentMethods($customerId);
            $this->stripeService->attachPaymentMethod($customerId, $paymentMethod->data[0]->id);
            $clientSecret = $this->stripeService->createPaymentIntent($setupFee, $customerId, $paymentMethod->data[0]->id);
            $subscriptionStatus = $this->stripeService->createSubscription($plan['name'], $plan['cycle'], $customerId);

            if ($clientSecret && $subscriptionStatus) {
                // PaymentMethod::create(['vendor_id' => isset($newVendor->id) ? $newVendor->id : $user->vendor->id, 'stripe_customer_id' => $customerId]);
                if($newVendor){
                    $newVendor->stripe_customer_id = $customerId;
                    $newVendor->save();
                }else{
                    $user->vendor->stripe_customer_id = $customerId;
                    $user->vendor->save();
                }
                
                $steps = [];
                for ($i = 1; $i <= 5; $i++) {
                    $steps[] = [
                        'vendor_id' => isset($newVendor->id) ? $newVendor->id : $user->vendor->id,
                        'progress_step_id' => $i,
                        'active' => ($i === 1),
                    ];
                }
                
                VendorProgress::insert($steps);

                $tags = ["new lead", "booked appointment", "customer"];
                $vendor = isset($newVendor) ? $newVendor : $user->vendor;
                $updates = [
                    'locationId' => 'kxsCgZcTUell5zwFkTUc', //Main Location To Manage All Users
                    'email' => $vendor->company_email,
                    'tags' => $tags,
                    'customFields' => [[
                        'plan_type' => $planName,
                        'onboarding' => "https://join.mymonstro.com/onboarding/{$vendor->id}",
                    ]],
                ];

                $this->ghlService->createContact($updates);
                DB::commit();
                return $this->sendResponse($vendor->id, 'Subscription successfull.');
            } else {
                return $this->sendError('Payment declined.', [], 500);
            }
        } catch (Exception $error) {
            DB::rollBack();
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function createUser($user){
        try{
            $password = str_replace(' ', '', $user['firstName']).'@'.Carbon::now()->year.'!';

            $user = User::create([
                'name' => $user['firstName'] .$user['lastName'],
                'email' => $user['email'],
                'password' => bcrypt($password),
                'email_verified_at' => now()
            ]);
            
            return $user;
        }catch (Exception $error) {
            return $error->getMessage();
        }
    }

}
