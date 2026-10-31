<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case InProduction = 'in_production';
    case Ready = 'ready';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جديد',
            self::Confirmed => 'مؤكد',
            self::InProduction => 'قيد التحضير',
            self::Ready => 'جاهز',
            self::OutForDelivery => 'في الطريق',
            self::Delivered => 'تم التسليم',
            self::Cancelled => 'ملغي',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::New => 'طلب جديد بانتظار المراجعة والتأكيد',
            self::Confirmed => 'تم تأكيد الطلب مع العميل',
            self::InProduction => 'المخبوزات قيد التحضير',
            self::Ready => 'الطلب جاهز للاستلام أو التوصيل',
            self::OutForDelivery => 'الطلب مع مندوب التوصيل',
            self::Delivered => 'تم تسليم الطلب بنجاح',
            self::Cancelled => 'تم إلغاء الطلب',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'amber',
            self::Confirmed => 'blue',
            self::InProduction => 'violet',
            self::Ready => 'teal',
            self::OutForDelivery => 'orange',
            self::Delivered => 'green',
            self::Cancelled => 'red',
        };
    }

    /** @return array<int, self> */
    public function nextStatuses(): array
    {
        return match ($this) {
            self::New => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::InProduction, self::Cancelled],
            self::InProduction => [self::Ready, self::Cancelled],
            self::Ready => [self::OutForDelivery, self::Delivered, self::Cancelled],
            self::OutForDelivery => [self::Delivered, self::Cancelled],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled], true);
    }

    public function isActive(): bool
    {
        return ! $this->isFinal();
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
