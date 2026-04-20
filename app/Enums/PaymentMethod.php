<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case GCash = 'gcash';
    case Maya = 'maya';
    case BankTransfer = 'bank_transfer';
    case Insurance = 'insurance';

    public function label(): string
    {
        return match($this) {
            self::Cash         => 'Cash',
            self::CreditCard   => 'Credit Card',
            self::DebitCard    => 'Debit Card',
            self::GCash        => 'GCash',
            self::Maya         => 'Maya',
            self::BankTransfer => 'Bank Transfer',
            self::Insurance    => 'Insurance',
        };
    }
}
