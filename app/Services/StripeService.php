<?php

namespace App\Services;

use Exception;
use Stripe\Subscription;

class StripeService
{
    protected $stripe;

    public function __construct(){
        \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));
        $this->stripe = new \Stripe\StripeClient();
    }

    public function createCustomer($vendor, $token){
        $customer = $this->stripe->customers->create([
            'name' => $vendor['firstName'] . ' ' . $vendor['lastName'],
            'email' => $vendor['email'],
            'phone' => $vendor['phone'],
            'source' => $token['id']
        ]);

        return $customer;
    }

    public function setupIntents($customer, $token){
        $setupIntents = $this->stripe->setupIntents->create([
            'customer' => $customer->id,
            'payment_method' => $token['card']['id']
        ]);

        return $setupIntents;
    }

    public function createPaymentIntent($amount, $customerId, $cardId){
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'automatic_payment_methods' => ['enabled' => true],
            'currency' => 'usd',
            'confirm' => true,
            'customer' => $customerId,
            'setup_future_usage' => 'off_session',
            'statement_descriptor' => 'mymonstro.com',
            'payment_method' => $cardId,
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
                        ['price' => 'price_1NVhbzDePDUzIffAUJAkGFHW'],
                    ],
                    'coupon' => 'k8NxIKxT',
                ],
            ],
            'standard' => [
                'month' => [
                    'items' => [
                        ['price' => 'price_1NVhpUDePDUzIffAFvqs2pzY'],
                    ],
                    'trial_period_days' => 14,
                ],
            ],
            'scale' => [
                'month' => [
                    'items' => [
                        ['price' => 'price_1NVhnhDePDUzIffA4BVwVB8r'],
                    ],
                    'trial_period_days' => 14,
                ],
                'annual' => [
                    'items' => [
                        ['price' => 'price_1NVhc0DePDUzIffAgcVGSraX'],
                    ],
                    'trial_period_days' => 0,
                ],
            ],
            'seo' => [
                'month' => [
                    'items' => [
                        ['price' => 'price_1NVhnhDePDUzIffA4BVwVB8r'],
                        ['price' => 'price_1O7OnVDePDUzIffAJrblSxgp'],
                    ],
                    'trial_period_days' => 0,
                ],
            ],
        ];

        return $subscriptions[$plan][$cycle] ?? null;
    }


}