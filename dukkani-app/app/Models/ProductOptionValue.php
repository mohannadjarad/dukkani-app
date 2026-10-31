<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionValue extends Model
{
    protected $fillable = ['product_option_id', 'value', 'price_delta_fils', 'sort_order'];

    protected function casts(): array
    {
        return ['price_delta_fils' => 'integer', 'sort_order' => 'integer'];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function deltaLabel(): string
    {
        return $this->price_delta_fils > 0
            ? '+ '.Money::format($this->price_delta_fils)
            : '';
    }
}
