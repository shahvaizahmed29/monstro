<?php

namespace App\Services;

use App\Models\MemberPayment;
use Exception;
use Illuminate\Support\Facades\Log;

class MemberPaymentService
{
  public function addPayment(array $payment, array $transaction) {
    try{
      $newPayment = MemberPayment::create($payment);
      $transactionService = new TransactionService();
      $newTransaction = $transactionService->addTransaction($transaction);

      return array("transaction" => $newTransaction, "payment" => $newPayment);
    } catch(Exception $error) {
      Log::info('===== Payment Service - addPayment() - error =====');
      Log::info($error->getMessage());
      return array("message" => $error->getMessage(), "error" => true);
    }

  }



}
