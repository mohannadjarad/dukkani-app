<?php

namespace App\Models;

use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvent extends Model
{
    protected $fillable = ['order_id', 'user_id', 'type', 'payload'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return match ($this->type) {
            'created' => 'تم إنشاء الطلب',
            'status_changed' => 'تحديث حالة الطلب',
            'deposit_paid' => 'تأكيد استلام العربون',
            'deposit_refunded' => 'استرجاع العربون',
            'deposit_updated' => 'تحديث حالة العربون',
            'note_added' => 'إضافة ملاحظة داخلية',
            'whatsapp_sent' => 'فتح رسالة واتساب',
            'customer_updated' => 'تحديث بيانات العميل',
            default => $this->type,
        };
    }

    public function detail(): ?string
    {
        return match ($this->type) {
            'status_changed' => isset($this->payload['from'], $this->payload['to'])
                ? ($this->payload['from']).' ← '.($this->payload['to'])
                : null,
            'note_added' => $this->payload['note'] ?? null,
            'whatsapp_sent' => isset($this->payload['template'])
                ? (WhatsAppService::TEMPLATES[$this->payload['template']] ?? $this->payload['template'])
                : null,
            'deposit_paid', 'deposit_refunded', 'deposit_updated' => $this->payload['label'] ?? null,
            default => null,
        };
    }

    public function icon(): string
    {
        return match ($this->type) {
            'created' => 'sparkles',
            'status_changed' => 'arrow',
            'deposit_paid' => 'cash',
            'deposit_refunded' => 'undo',
            'deposit_updated' => 'cash',
            'note_added' => 'note',
            'whatsapp_sent' => 'chat',
            'customer_updated' => 'user',
            default => 'dot',
        };
    }

    public function color(): string
    {
        return match ($this->type) {
            'deposit_paid', 'deposit_refunded' => 'emerald',
            'note_added' => 'gold',
            'whatsapp_sent' => 'sage',
            'status_changed' => 'brand',
            default => 'stone',
        };
    }
}
