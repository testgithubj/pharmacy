<?php


namespace App\Service;


use App\Enum\AccountEnum;
use App\Models\Transaction;

class TransactionService
{
    public static function saleTransaction($amout, $invoiceId)
    {
        try {
            return Transaction::create([
                'tran_id' => uniqid(),
                'date' => now(),
                'debit_account_id' => AccountEnum::ACCOUNTS_RECEIVABLE,
                'credit_account_id' => AccountEnum::SALES,
                'amount' => $amout,
                'invoice_type' => 'sale',
                'invoice_id' => $invoiceId,
                'particular' => 'Payment Receive on Sale '.$invoiceId,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function purchaseTransaction($amout, $invoiceId)
    {
        try {
            return Transaction::create([
                'tran_id' => uniqid(),
                'date' => now(),
                'debit_account_id' => AccountEnum::COST_OF_SALES,
                'credit_account_id' => AccountEnum::ACCOUNTS_PAYABLE,
                'amount' => $amout,
                'invoice_type' => 'purchase',
                'invoice_id' => $invoiceId,
                'particular' => 'Paid on Purchase Invoice '. $invoiceId,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function expenseTransaction($amout, $accountId, $desc = null)
    {
        try {
            return Transaction::create([
                'tran_id' => uniqid(),
                'date' => now(),
                'debit_account_id' => $accountId,
                'credit_account_id' => AccountEnum::ACCOUNTS_PAYABLE,
                'amount' => $amout,
                'particular' => $desc,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }


}