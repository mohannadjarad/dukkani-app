<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    protected $fillable = ['store_id', 'order_id', 'channel', 'template', 'to', 'status', 'body', 'sent_at'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function templateLabel(): string
    {
        return match ($this->template) {
            'order_confirmation' => 'تأكيد الطلب',
            'order_ready' => 'الطلب جاهز',
            'out_for_delivery' => 'في الطريق',
            'delivered' => 'تم التسليم',
            'deposit_reminder' => 'تذكير بالعربون',
            'follow_up' => 'متابعة العميل',
            default => $this->template,
        };
    }
}
