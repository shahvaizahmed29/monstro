<?php

namespace App\Services;

use Exception;
use Stripe\Subscription;

class StripeService
{
    protected $stripe;

    public function __construct(){
        \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));
        // $this->stripe = new \Stripe\StripeClient();
    }

    public function createCustomer($vendor, $token){
        $stripe = new \Stripe\StripeClient(['api_key' => config('services.stripe.secret_key')]);
        $customer = $stripe->customers->create([
            'name' => $vendor['firstName'] . ' ' . $vendor['lastName'],
            'email' => $vendor['email'],
            'phone' => $vendor['phone'],
            'source' => $token
        ]);

        return $customer;
    }

    public function setupIntents($customer, $token){
        $stripe = new \Stripe\StripeClient(['api_key' => config('services.stripe.secret_key')]);
        $setupIntents = $stripe->setupIntents->create([
            'customer' => $customer->id,
            'payment_method' => $token['card']['id']
        ]);

        return $setupIntents;
    }

    public function createPaymentIntent($amount, $customerId, $cardId){
        $stripe = new \Stripe\StripeClient(['api_key' => config('services.stripe.secret_key')]);
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $amount,
            'automatic_payment_methods' => ['enabled' => true],
            'currency' => 'usd',
            'confirm' => true,
            'customer' => $customerId,
            'setup_future_usage' => 'off_session',
            'statement_descriptor' => 'mymonstro.com',
            // 'payment_method' => $cardId,
            'return_url' => 'https://mymonstro.com',
        ]);

        return $paymentIntent;
    }

    public function createSubscription($plan, $cycle, $customer){
        try {
            $subscriptionParams = $this->getSubscriptionParams($plan, $cycle);
            $subscription = Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    [
                        'price' => $subscriptionParams['price'],
                        'quantity' => $subscriptionParams['quantity'],
                    ],
                ],
            ]);
            return $subscription;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    protected function getSubscriptionParams($plan, $cycle)
    {
        $subscriptions = [
            'lite' => [
                'month' => [
                    'items' => [
                        ['price' => 'price_1ORZbhJ7qtdSRbE22L5M4wtz'],
                    ],
                    'coupon' => '3mv0meNa',
                ],
            ],
            'standard' => [
                'month' => [
                    'items' => [
                        ['price' => 'price_1ORZinJ7qtdSRbE2P8ENDrDU'],
                    ],
                    'trial_period_days' => 14,
                ],
            ],
            'scale' => [
                'month' => [
                    'items' => [
                        ['price' => 'price_1ORZkGJ7qtdSRbE2dMDUxAKY'],
                    ],
                    'trial_period_days' => 14,
                ],
                'annual' => [
                    'items' => [
                        ['price' => 'price_1ORZkGJ7qtdSRbE2vAIsBSUG'],
                    ],
                    'trial_period_days' => 0,
                ],
            ],
            'seo' => [
                'month' => [
                    'items' => [
                        ['price' => 'price_1ORZnhJ7qtdSRbE2tTw1HoHn'],
                        ['price' => 'price_1ORZnHJ7qtdSRbE2yWYiausp'],
                    ],
                    'trial_period_days' => 0,
                ],
            ],
        ];

        return $subscriptions[$plan][$cycle] ?? null;
    }


}