<?php

namespace App\Models;

use App\Support\Money;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'name', 'phone', 'email', 'address', 'notes', 'tags'];

    protected function casts(): array
    {
        return ['tags' => 'array'];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * A wa.me link that opens a chat with this customer, so the seller can send
     * WhatsApp messages to the customer directly. Numbers are normalized with
     * the store's country dial code.
     */
    public function whatsappLink(?string $text = null): ?string
    {
        return Phone::whatsappLink($this->phone, $text, $this->dialCode());
    }

    public function hasPhone(): bool
    {
        return Phone::normalize($this->phone, $this->dialCode()) !== null;
    }

    protected function dialCode(): string
    {
        return $this->store?->dialCode() ?? '962';
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%"));
    }

    public function totalSpent(): int
    {
        return (int) $this->orders()->where('status', 'delivered')->sum('total_fils');
    }

    public function formattedSpent(): string
    {
        return Money::format($this->totalSpent());
    }

    public function lastOrderAt()
    {
        return $this->orders()->latest('created_at')->value('created_at');
    }

    public function isRepeat(): bool
    {
        return $this->orders()->count() > 1;
    }
}
