<?php

namespace App\Models;

use App\Enums\DepositStatus;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'customer_id', 'number', 'public_token', 'status',
        'fulfillment_type', 'scheduled_at', 'delivery_address', 'subtotal_fils',
        'delivery_fee_fils', 'discount_fils', 'total_fils', 'deposit_fils',
        'deposit_status', 'payment_method', 'balance_due_fils', 'customer_notes',
        'internal_notes', 'source', 'reference_image_path', 'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'fulfillment_type' => FulfillmentType::class,
            'deposit_status' => DepositStatus::class,
            'payment_method' => PaymentMethod::class,
            'scheduled_at' => 'datetime',
            'consent_at' => 'datetime',
            'subtotal_fils' => 'integer',
            'delivery_fee_fils' => 'integer',
            'discount_fils' => 'integer',
            'total_fils' => 'integer',
            'deposit_fils' => 'integer',
            'balance_due_fils' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->public_token ??= (string) Str::uuid();
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    public function scopeForStore(Builder $query, Store|int $store): Builder
    {
        return $query->where('store_id', $store instanceof Store ? $store->id : $store);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('number', 'like', "%{$term}%")
                ->orWhereHas('customer', fn (Builder $c) => $c
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
        });
    }

    public function publicUrl(): string
    {
        return route('track.show', $this->public_token);
    }

    public function totalFormatted(): string
    {
        return Money::format($this->total_fils);
    }

    public function subtotalFormatted(): string
    {
        return Money::format($this->subtotal_fils);
    }

    public function depositFormatted(): string
    {
        return Money::format($this->deposit_fils);
    }

    public function balanceFormatted(): string
    {
        return Money::format($this->balance_due_fils);
    }

    public function depositAmount(): int
    {
        return $this->deposit_fils;
    }

    public function computeBalance(): int
    {
        return max(0, $this->total_fils - ($this->deposit_status === DepositStatus::Paid ? $this->deposit_fils : 0));
    }

    public function nextStatuses(): array
    {
        return $this->status->nextStatuses();
    }

    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isFuture() && $this->status->isActive();
    }

    public function itemsCount(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function itemsSummary(): string
    {
        return $this->items->map(fn (OrderItem $item) => $item->product_name.' ×'.$item->quantity)->join('، ');
    }

    public function statusColor(): string
    {
        return $this->status->color();
    }

    public function isLate(): bool
    {
        return $this->isUpcoming() === false
            && $this->scheduled_at->isPast()
            && ! $this->status->isFinal();
    }
}
