<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function raw(Store $store): array
    {
        return session()->get('cart.'.$store->id, []);
    }

    public function put(Store $store, array $lines): void
    {
        session()->put('cart.'.$store->id, $lines);
    }

    public function add(Store $store, Product $product, int $quantity, array $optionValueIds = [], ?string $notes = null): string
    {
        abort_unless($product->store_id === $store->id, 404);

        if (! $product->is_active) {
            throw ValidationException::withMessages(['product' => 'هذا المنتج غير متوفر حالياً.']);
        }

        $quantity = max(1, min(20, $quantity));
        $product->loadMissing('options.values');

        $selected = [];

        foreach ($product->options as $option) {
            $value = $option->values->firstWhere('id', (int) ($optionValueIds[$option->id] ?? 0));

            if ($option->is_required && ! $value) {
                throw ValidationException::withMessages(['options' => "الرجاء اختيار «{$option->name}»."]);
            }

            if ($value) {
                $selected[] = [
                    'option_id' => $option->id,
                    'value_id' => $value->id,
                    'name' => $option->name,
                    'value' => $value->value,
                    'delta' => (int) $value->price_delta_fils,
                ];
            }
        }

        $key = $this->lineKey($product->id, $selected);
        $lines = $this->raw($store);
        $existing = $lines[$key] ?? null;

        $lines[$key] = [
            'product_id' => $product->id,
            'quantity' => min(20, ($existing['quantity'] ?? 0) + $quantity),
            'options' => $selected,
            'notes' => $notes ?: ($existing['notes'] ?? null),
        ];

        $this->put($store, $lines);

        return $key;
    }

    public function updateQuantity(Store $store, string $key, int $quantity): void
    {
        $lines = $this->raw($store);

        if (! isset($lines[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($lines[$key]);
        } else {
            $lines[$key]['quantity'] = min(20, $quantity);
        }

        $this->put($store, $lines);
    }

    public function remove(Store $store, string $key): void
    {
        $lines = $this->raw($store);
        unset($lines[$key]);
        $this->put($store, $lines);
    }

    public function clear(Store $store): void
    {
        session()->forget('cart.'.$store->id);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function items(Store $store): Collection
    {
        $lines = $this->raw($store);

        if (empty($lines)) {
            return collect();
        }

        $products = Product::whereIn('id', collect($lines)->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        return collect($lines)
            ->map(function (array $line, string $key) use ($products) {
                $product = $products->get($line['product_id']);

                if (! $product) {
                    return null;
                }

                $delta = collect($line['options'] ?? [])->sum('delta');
                $unit = $product->price_fils + $delta;

                return [
                    'key' => $key,
                    'product' => $product,
                    'quantity' => (int) $line['quantity'],
                    'options' => $line['options'] ?? [],
                    'notes' => $line['notes'] ?? null,
                    'unit_price_fils' => $unit,
                    'line_total_fils' => $unit * (int) $line['quantity'],
                ];
            })
            ->filter()
            ->values();
    }

    public function subtotal(Store $store): int
    {
        return (int) $this->items($store)->sum('line_total_fils');
    }

    public function count(Store $store): int
    {
        return (int) $this->items($store)->sum('quantity');
    }

    public function isEmpty(Store $store): bool
    {
        return $this->items($store)->isEmpty();
    }

    public function deliveryFee(Store $store, string $fulfillment = 'delivery'): int
    {
        return $fulfillment === 'delivery' && $store->delivery_enabled
            ? $store->delivery_fee_fils
            : 0;
    }

    public function depositFor(Store $store, int $total, string $paymentMethod): int
    {
        if ($paymentMethod === 'cash' || $store->deposit_percent <= 0) {
            return 0;
        }

        return (int) round($total * $store->deposit_percent / 100);
    }

    protected function lineKey(int $productId, array $selected): string
    {
        $values = collect($selected)->pluck('value_id')->sort()->implode('-');

        return substr(md5($productId.'|'.$values), 0, 12);
    }
}
