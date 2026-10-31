<?php

namespace App\Enums;

enum DepositStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::None => 'بدون عربون',
            self::Pending => 'عربون معلّق',
            self::Paid => 'عربون مدفوع',
            self::Refunded => 'عربون مسترجع',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::None => 'stone',
            self::Pending => 'amber',
            self::Paid => 'green',
            self::Refunded => 'red',
        };
    }
}
