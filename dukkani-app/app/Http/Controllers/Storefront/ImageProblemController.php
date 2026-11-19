<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Services\ImageHealthService;
use Illuminate\Http\Response;

/**
 * A storefront image failed to load in a real browser. The page beacons it back
 * so the owner finds out from her dashboard — not from a customer who thought
 * the shop was abandoned. The URL is signed and short-lived, so the endpoint
 * cannot be poked by hand to flag healthy images.
 *
 * The browser's failure is only a hint: the server checks the stored URL before
 * flagging it. One flaky mobile connection must not hide a photo permanently.
 */
class ImageProblemController extends Controller
{
    public function __invoke(Store $store, Product $product, ImageHealthService $health): Response
    {
        abort_unless($product->store_id === $store->id, 404);

        if (! $health->loads($product)) {
            $product->markImageBroken();
        }

        return response()->noContent();
    }
}
