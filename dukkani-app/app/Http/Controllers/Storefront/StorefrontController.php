<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Services\CartService;
use Illuminate\View\View;

abstract class StorefrontController extends Controller
{
    public function __construct(protected CartService $cart) {}

    protected function render(Store $store, string $view, array $data = []): View
    {
        return view($view, array_merge([
            'store' => $store,
            'cartCount' => $this->cart->count($store),
            'cartSubtotal' => $this->cart->subtotal($store),
        ], $data));
    }

    protected function ensureProductInStore(Store $store, Product $product): void
    {
        abort_unless($product->store_id === $store->id && $product->is_active, 404);
    }
}