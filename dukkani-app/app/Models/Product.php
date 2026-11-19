<?php

namespace App\Models;

use App\Services\ImageService;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id', 'category_id', 'name', 'slug', 'description', 'price_fils',
        'image_path', 'image_public_id', 'image_broken_at', 'lead_time_hours', 'is_active',
        'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_fils' => 'integer',
            'lead_time_hours' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'image_broken_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function imageUrl(): string
    {
        return $this->resolveImageUrl('f_auto,q_auto,w_800');
    }

    public function thumbnailUrl(): string
    {
        return $this->resolveImageUrl('f_auto,q_auto,w_400');
    }

    /** The card image visitors actually see: a dead link falls back cleanly. */
    public function displayThumbnailUrl(): string
    {
        return $this->imageIsBroken() ? static::placeholderUrl() : $this->thumbnailUrl();
    }

    /** The large image visitors actually see: a dead link falls back cleanly. */
    public function displayImageUrl(): string
    {
        return $this->imageIsBroken() ? static::placeholderUrl() : $this->imageUrl();
    }

    /**
     * The neutral product graphic. Demo mode keeps the Amal Bakes artwork while
     * a real store never shows another brand.
     */
    public static function placeholderUrl(): string
    {
        return asset(config('app.demo_mode')
            ? 'images/placeholder-bakery.svg'
            : 'images/placeholder-product.svg');
    }

    public function hasImage(): bool
    {
        return filled($this->image_path);
    }

    /**
     * A stored image that no longer loads — a dead link, a deleted Cloudinary
     * asset, or a 404. Products with no image at all are not "broken", they
     * simply show the neutral placeholder.
     */
    public function imageIsBroken(): bool
    {
        return $this->hasImage() && $this->image_broken_at !== null;
    }

    /** Owner-facing sentence naming the product, or null when the image is fine. */
    public function imageIssueText(): ?string
    {
        return $this->imageIsBroken()
            ? 'في مشكلة في صورة المنتج «'.$this->name.'» — الرابط المخزَّن ما بيفتح.'
            : null;
    }

    /** Flag the image as broken. Returns true only when the state changed. */
    public function markImageBroken(): bool
    {
        if (! $this->hasImage() || $this->image_broken_at !== null) {
            return false;
        }

        $this->forceFill(['image_broken_at' => now()])->saveQuietly();

        return true;
    }

    /** Clear the flag once the image loads again. */
    public function markImageHealthy(): bool
    {
        if ($this->image_broken_at === null) {
            return false;
        }

        $this->forceFill(['image_broken_at' => null])->saveQuietly();

        return true;
    }

    /** @param  Builder<Product>  $query */
    public function scopeWithImageIssue(Builder $query): Builder
    {
        return $query->whereNotNull('image_path')->whereNotNull('image_broken_at');
    }

    protected function resolveImageUrl(string $transform): string
    {
        if (! $this->image_path) {
            return static::placeholderUrl();
        }

        if (str_starts_with($this->image_path, 'http')) {
            $images = app(ImageService::class);

            // Legacy local uploads stored an APP_URL-based link. Resolve it
            // against the host the visitor is actually using.
            if ($local = $images->localStoragePath($this->image_path)) {
                return asset('storage/'.$local);
            }

            return $images->url($this->image_path, $transform);
        }

        return str_starts_with($this->image_path, 'images/')
            ? asset($this->image_path)
            : asset('storage/'.$this->image_path);
    }

    public function priceFormatted(): string
    {
        return Money::format($this->price_fils);
    }

    public function minLeadTime(): int
    {
        return $this->lead_time_hours ?: ($this->store?->lead_time_hours ?? 48);
    }
}
