<?php

namespace App\Tests\DataProvider\Entity\Stripe\Mock;

use Stripe\BankAccount;

class MockStripeBankAccount
{
    public static function getStripeBankAccount()
    {
        $bankAccount = new BankAccount('ba_123');
        $bankAccount->account = 'acct_123';
        $bankAccount->bank_name = 'STRIPE TEST BANK';
        $bankAccount->country = 'FR';
        $bankAccount->currency = 'EUR';
        $bankAccount->last4 = '1234';
        $bankAccount->status = 'verified';
        $bankAccount->account_holder_name = 'John Doe';
        $bankAccount->account_holder_type = 'individual';

        return $bankAccount;
    }
}
