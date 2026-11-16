<?php

namespace App\Models;

use App\Services\ImageService;
use App\Support\Money;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'name', 'slug', 'tagline', 'description', 'logo_path',
        'logo_public_id', 'cover_path', 'cover_public_id', 'phone', 'whatsapp',
        'instagram', 'city', 'address',
        'currency', 'country_code', 'delivery_fee_fils', 'pickup_enabled',
        'delivery_enabled', 'lead_time_hours', 'bank_name', 'bank_account_name',
        'bank_iban', 'timezone',
        'status', 'onboarding_completed_at',
        'plan', 'plan_expires_at', 'is_trial', 'order_limit_override', 'first_paid_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee_fils' => 'integer',
            'pickup_enabled' => 'boolean',
            'delivery_enabled' => 'boolean',
            'lead_time_hours' => 'integer',
            'onboarding_completed_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'is_trial' => 'boolean',
            'order_limit_override' => 'integer',
            'first_paid_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function hours(): HasMany
    {
        return $this->hasMany(StoreHour::class)->orderBy('day_of_week');
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class)->orderBy('date');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(StoreNotification::class);
    }

    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    public function imageUrl(?string $path = null, string $transform = 'f_auto,q_auto,w_800'): ?string
    {
        $path ??= $this->logo_path;

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return app(ImageService::class)->url($path, $transform);
        }

        return str_starts_with($path, 'images/')
            ? asset($path)
            : asset('storage/'.$path);
    }

    public function logoUrl(): ?string
    {
        return $this->imageUrl($this->logo_path);
    }

    public function coverUrl(): ?string
    {
        return $this->imageUrl($this->cover_path);
    }

    public function todayHours(): ?StoreHour
    {
        $day = (int) now()->dayOfWeek;

        return $this->hours->firstWhere('day_of_week', $day)
            ?? $this->hours()->where('day_of_week', $day)->first();
    }

    public function isOpenNow(): bool
    {
        $today = $this->todayHours();

        if (! $today || $today->is_closed || ! $today->open_time || ! $today->close_time) {
            return false;
        }

        $now = now()->format('H:i:s');
        $open = $today->open_time;
        $close = $today->close_time;

        if ($close < $open) {
            return $now >= $open || $now <= $close;
        }

        return $now >= $open && $now <= $close;
    }

    /**
     * Country dial code used to normalize phone numbers, for example 962 for
     * Jordan or 970 for Palestine.
     */
    public function dialCode(): string
    {
        return $this->country_code ?: '962';
    }

    public function currencyCode(): string
    {
        return strtoupper($this->currency ?: (string) config('app.currency', 'JOD'));
    }

    public function currencySymbol(): string
    {
        return Money::symbol($this->currencyCode());
    }

    /**
     * Payment methods this store may offer, in display order. CliQ is only
     * available in Jordan.
     */
    public function allowedPaymentMethods(): array
    {
        $methods = ['cash'];

        if (in_array($this->dialCode(), config('payments.cliq_countries', ['962']), true)) {
            $methods[] = 'cliq';
        }

        $methods[] = 'bank_transfer';

        return $methods;
    }

    /**
     * Whether the store has configured bank-transfer details the customer can
     * pay the full order amount to.
     */
    public function hasBankDetails(): bool
    {
        return filled($this->bank_name)
            || filled($this->bank_account_name)
            || filled($this->bank_iban);
    }

    public function whatsappLink(?string $text = null): string
    {
        return Phone::whatsappLink($this->whatsapp ?: $this->phone, $text, $this->dialCode())
            ?? 'https://wa.me/'.$this->dialCode().'790000000';
    }

    public function mapsUrl(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode(($this->address ?: '').' '.($this->city ?: 'عمان'));
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }
}
