<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'quantity', 'unit_price_fils',
        'options', 'notes', 'line_total_fils',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_fils' => 'integer',
            'line_total_fils' => 'integer',
            'options' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotalFormatted(): string
    {
        return Money::format($this->line_total_fils);
    }

    public function optionsLabel(): string
    {
        return collect($this->options ?? [])
            ->map(fn ($option) => ($option['value'] ?? '').(($option['delta'] ?? 0) > 0 ? ' (+'.Money::format($option['delta']).')' : ''))
            ->filter()
            ->join(' · ');
    }
}
