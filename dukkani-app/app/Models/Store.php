<?php

namespace App\Models;

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
        'cover_path', 'phone', 'whatsapp', 'instagram', 'city', 'address',
        'currency', 'delivery_fee_fils', 'deposit_percent', 'pickup_enabled',
        'delivery_enabled', 'lead_time_hours', 'deposit_instructions', 'timezone',
        'status', 'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee_fils' => 'integer',
            'deposit_percent' => 'integer',
            'pickup_enabled' => 'boolean',
            'delivery_enabled' => 'boolean',
            'lead_time_hours' => 'integer',
            'onboarding_completed_at' => 'datetime',
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

    public function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
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

    public function whatsappLink(?string $text = null): string
    {
        return Phone::whatsappLink($this->whatsapp ?: $this->phone ?: '962790000000', $text)
            ?? 'https://wa.me/962790000000';
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
