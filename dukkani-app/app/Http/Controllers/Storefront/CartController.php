<?php

namespace App\Http\Controllers\Storefront;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends StorefrontController
{
    public function index(Store $store): View
    {
        $items = $this->cart->items($store);
        $subtotal = (int) $items->sum('line_total_fils');
        $inCart = $items->pluck('product.id')->all();

        $suggestions = $store->activeProducts()
            ->with('category')
            ->whereNotIn('id', $inCart)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        return $this->render($store, 'storefront.cart', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryFee' => $store->delivery_enabled ? (int) $store->delivery_fee_fils : 0,
            'suggestions' => $suggestions,
        ]);
    }

    public function add(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:200'],
        ], [
            'product_id.required' => 'اختاري منتجاً أولاً.',
            'quantity.required' => 'حددي الكمية.',
            'quantity.min' => 'أقل كمية هي قطعة واحدة.',
            'quantity.max' => 'أقصى كمية للطلب الواحد هي ٢٠ قطعة.',
            'notes.max' => 'الملاحظات طويلة جداً، اختصريها قليلاً.',
        ]);

        $product = $store->activeProducts()->findOrFail($data['product_id']);

        $this->cart->add(
            $store,
            $product,
            (int) $data['quantity'],
            $data['options'] ?? [],
            $data['notes'] ?? null,
        );

        return redirect()
            ->route('storefront.product', [$store, $product])
            ->with('status', 'أُضيف «'.$product->name.'» إلى سلتك.');
    }

    public function update(Request $request, Store $store, string $key): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ], [
            'quantity.required' => 'حددي الكمية.',
            'quantity.max' => 'أقصى كمية للطلب الواحد هي ٢٠ قطعة.',
        ]);

        $this->cart->updateQuantity($store, $key, (int) $data['quantity']);

        return redirect()
            ->route('storefront.cart', $store)
            ->with('status', 'تم تحديث سلتك.');
    }

    public function remove(Store $store, string $key): RedirectResponse
    {
        $this->cart->remove($store, $key);

        return redirect()
            ->route('storefront.cart', $store)
            ->with('status', 'تم حذف المنتج من السلة.');
    }

    public function clear(Store $store): RedirectResponse
    {
        $this->cart->clear($store);

        return redirect()
            ->route('storefront.cart', $store)
            ->with('status', 'تم تفريغ السلة.');
    }
}