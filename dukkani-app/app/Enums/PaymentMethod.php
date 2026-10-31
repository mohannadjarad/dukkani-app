<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Cliq = 'cliq';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقداً عند الاستلام',
            self::Cliq => 'تحويل CliQ',
            self::BankTransfer => 'حوالة بنكية',
        };
    }

    public function requiresDeposit(): bool
    {
        return $this !== self::Cash;
    }
}
