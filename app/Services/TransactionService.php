<?php

namespace App\Services;

use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Log;

class TransactionService
{
  public function addTransaction($transaction) {
    try{
      return Transaction::create($transaction);
    } catch(Exception $error) {
      Log::info('===== Transaction Service - addPayment() - error =====');
      Log::info($error->getMessage());
      return array("message" => $error->getMessage(), "error" => true);
    }

  }



}
