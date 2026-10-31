<?php

namespace App\Http\Controllers\Storefront;

use App\Models\Product;
use App\Models\Store;
use Illuminate\View\View;

class ShopController extends StorefrontController
{
    public function home(Store $store): View
    {
        $store->load('hours');

        $featured = $store->activeProducts()
            ->with('category')
            ->featured()
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        if ($featured->count() < 4) {
            $featured = $store->activeProducts()
                ->with('category')
                ->orderBy('sort_order')
                ->take(6)
                ->get();
        }

        $categories = $store->categories()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->get();

        return $this->render($store, 'storefront.home', [
            'featured' => $featured,
            'categories' => $categories,
            'productCount' => $store->activeProducts()->count(),
        ]);
    }

    public function menu(Store $store): View
    {
        $categoryId = (int) request('category');
        $search = trim((string) request('q'));

        $categories = $store->categories()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->get();

        $products = $store->activeProducts()
            ->with('category')
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->orderBy('sort_order')
            ->get();

        return $this->render($store, 'storefront.menu', [
            'categories' => $categories,
            'products' => $products,
            'categoryId' => $categoryId,
            'search' => $search,
            'totalProducts' => $store->activeProducts()->count(),
        ]);
    }

    public function product(Store $store, Product $product): View
    {
        $this->ensureProductInStore($store, $product);

        $store->load('hours');
        $product->load(['options.values', 'category']);

        $related = $store->activeProducts()
            ->with('category')
            ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
            ->whereKeyNot($product->id)
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        $optionPayload = $product->options->map(fn ($option) => [
            'id' => $option->id,
            'name' => $option->name,
            'required' => $option->is_required,
            'values' => $option->values->map(fn ($value) => [
                'id' => $value->id,
                'value' => $value->value,
                'delta' => (int) $value->price_delta_fils,
            ])->all(),
        ])->all();

        $selected = $product->options
            ->filter(fn ($option) => $option->is_required && $option->values->isNotEmpty())
            ->mapWithKeys(fn ($option) => [(string) $option->id => (string) $option->values->first()->id])
            ->all();

        $deltas = $product->options
            ->flatMap(fn ($option) => $option->values->mapWithKeys(
                fn ($value) => [$option->id.':'.$value->id => (int) $value->price_delta_fils]
            ))
            ->all();

        return $this->render($store, 'storefront.product', [
            'product' => $product,
            'related' => $related,
            'optionPayload' => $optionPayload,
            'productConfig' => [
                'base' => (int) $product->price_fils,
                'options' => $optionPayload,
                'selected' => $selected,
                'deltas' => $deltas,
            ],
        ]);
    }
}