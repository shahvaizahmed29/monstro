<?php

namespace App\Services;

use App\Models\StripePlan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MemberStripeService
{
  protected $stripe;
  private $accessKey;

  public function __construct($accessKey)
  {
    $this->accessKey = $accessKey;
  }

  public function completePayment(Request $request) {
    $stripe = new \Stripe\StripeClient(['api_key' => $this->accessKey]);
    Log::info($this->accessKey);
    $oldCustomer = $stripe->customers->search([
      'query' => 'email:\'' . $request->email . '\'',
    ]);

    try{
      if (count($oldCustomer['data'])) {
        $customer = $oldCustomer['data'][0];
    } else {
        $customer = $stripe->customers->create([
            'name' => $request->firstName.' '.$request->lastName,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        // Get the token from the request
        $token = $request["billing"]["stripeToken"];
        Log::info($token);

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
        $planId = $request->planId;
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
              return $payment;
        } else {
            $stripe->subscriptions->create([
                'customer' => $customer['id'],
                'items' => [
                    ['price' => $stripePlan->pricing->stripe_price_id],
                ],
            ]);
            return $customer;
        }
    }
    } catch(Exception $error) {
      DB::rollBack();
      Log::info('===== PaymentController - subscribe() - error =====');
      Log::info($error->getMessage());
      return array("message" => $error->getMessage(), "error" => true);
    }

  }

}
