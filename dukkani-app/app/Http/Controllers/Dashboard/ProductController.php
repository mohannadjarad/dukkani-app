<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $store = $this->currentStore();

        $filters = [
            'q' => trim($request->string('q')->toString()),
            'category' => $request->integer('category') ?: 'all',
            'status' => $this->inList($request->string('status')->toString(), ['all', 'active', 'hidden'], 'all'),
            'sort' => $this->inList($request->string('sort')->toString(), ['recent', 'price', 'name'], 'recent'),
        ];

        $query = Product::where('store_id', $store->id)
            ->with('category')
            ->withCount('options');

        if ($filters['q'] !== '') {
            $query->where(fn ($q) => $q
                ->where('name', 'like', '%'.$filters['q'].'%')
                ->orWhere('description', 'like', '%'.$filters['q'].'%'));
        }

        if ($filters['category'] !== 'all') {
            $query->where('category_id', $filters['category']);
        }

        match ($filters['status']) {
            'active' => $query->where('is_active', true),
            'hidden' => $query->where('is_active', false),
            default => null,
        };

        match ($filters['sort']) {
            'price' => $query->orderByDesc('price_fils'),
            'name' => $query->orderBy('name'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = $store->categories()->withCount('products')->get();

        $stats = [
            'total' => Product::where('store_id', $store->id)->count(),
            'active' => Product::where('store_id', $store->id)->where('is_active', true)->count(),
            'hidden' => Product::where('store_id', $store->id)->where('is_active', false)->count(),
            'categories' => $categories->count(),
        ];

        return view('dashboard.products.index', [
            'store' => $store,
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $store = $this->currentStore();

        return view('dashboard.products.form', [
            'store' => $store,
            'product' => null,
            'categories' => $store->categories()->get(),
            'options' => $this->normalizeOptions(old('options', [])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $this->currentStore();
        $data = $this->validated($request, $store);

        $product = DB::transaction(function () use ($store, $data, $request) {
            $product = Product::create([
                'store_id' => $store->id,
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name']),
                'description' => $data['description'] ?? null,
                'price_fils' => Money::jod($data['price']),
                'image_path' => $this->resolveImage($request),
                'lead_time_hours' => $data['lead_time_hours'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'sort_order' => (int) (Product::where('store_id', $store->id)->max('sort_order') + 1),
            ]);

            $this->syncOptions($product, $data['options'] ?? []);

            return $product;
        });

        return redirect()
            ->route('products.index')
            ->with('status', 'تمت إضافة «'.$product->name.'» إلى المنيو.');
    }

    public function edit(Product $product): View
    {
        $store = $this->authorizeProduct($product);
        $product->load('options.values');

        $options = $product->options->map(fn ($option) => [
            'name' => $option->name,
            'type' => $option->type,
            'is_required' => $option->is_required,
            'values' => $option->values->map(fn ($value) => [
                'value' => $value->value,
                'price_delta' => $value->price_delta_fils / 1000,
            ])->all(),
        ])->all();

        return view('dashboard.products.form', [
            'store' => $store,
            'product' => $product,
            'categories' => $store->categories()->get(),
            'options' => $this->normalizeOptions(old('options', $options)),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $store = $this->authorizeProduct($product);
        $data = $this->validated($request, $store, $product);

        DB::transaction(function () use ($product, $request, $data) {
            $product->update([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->uniqueSlug(($data['slug'] ?? '') ?: $data['name'], $product),
                'description' => $data['description'] ?? null,
                'price_fils' => Money::jod($data['price']),
                'image_path' => $this->resolveImage($request, $product),
                'lead_time_hours' => $data['lead_time_hours'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
            ]);

            $this->syncOptions($product, $data['options'] ?? []);
        });

        return redirect()
            ->route('products.index')
            ->with('status', 'تم تحديث «'.$product->name.'».');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', $product->is_active
            ? '«'.$product->name.'» ظاهر الآن في المتجر.'
            : 'تم إخفاء «'.$product->name.'» من المتجر.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $name = $product->name;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'تم حذف «'.$name.'» من المنيو.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $store = $this->currentStore();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
        ], [
            'name.required' => 'اكتبي اسم التصنيف.',
            'name.min' => 'اسم التصنيف قصير جداً.',
            'name.max' => 'اسم التصنيف طويل جداً.',
        ]);

        $exists = $store->categories()->where('name', $data['name'])->exists();

        if ($exists) {
            throw ValidationException::withMessages(['name' => 'هذا التصنيف موجود مسبقاً.']);
        }

        $store->categories()->create([
            'name' => $data['name'],
            'sort_order' => (int) ($store->categories()->max('sort_order') + 1),
            'is_active' => true,
        ]);

        return back()->with('status', 'تمت إضافة التصنيف «'.$data['name'].'».');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        $store = $this->currentStore();

        abort_unless($category->store_id === $store->id, 404);

        $name = $category->name;
        $category->delete();

        return back()->with('status', 'تم حذف التصنيف «'.$name.'» وسيبقى المنتجون بدون تصنيف.');
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, Store $store, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.1', 'max:5000'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('store_id', $store->id)],
            'lead_time_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'image_url' => ['nullable', 'string', 'max:600'],
            'image_file' => ['nullable', 'image', 'max:2048'],
            'options' => ['nullable', 'array', 'max:10'],
            'options.*.name' => ['required', 'string', 'max:60'],
            'options.*.type' => ['nullable', Rule::in(['single', 'multiple'])],
            'options.*.values' => ['required', 'array', 'min:1', 'max:20'],
            'options.*.values.*.value' => ['required', 'string', 'max:80'],
            'options.*.values.*.price_delta' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ], [
            'name.required' => 'اسم المنتج مطلوب.',
            'name.min' => 'اسم المنتج قصير جداً.',
            'price.required' => 'أدخلي سعر المنتج.',
            'price.numeric' => 'السعر يجب أن يكون رقماً.',
            'price.min' => 'السعر يجب أن يكون أكبر من صفر.',
            'image_file.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'image_file.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
            'options.*.name.required' => 'كل خيار يحتاج اسماً.',
            'options.*.values.required' => 'كل خيار يحتاج قيمة واحدة على الأقل.',
            'options.*.values.*.value.required' => 'أدخلي قيمة لكل اختيار.',
            'options.*.values.*.price_delta.numeric' => 'فرق السعر يجب أن يكون رقماً.',
        ]);

        if (($data['image_url'] ?? null) && ! preg_match('~^https?://~i', $data['image_url'])) {
            throw ValidationException::withMessages(['image_url' => 'أدخلي رابط صورة يبدأ بـ http أو https.']);
        }

        return $data;
    }

    /**
     * Normalize option rows coming from the model or from old() input so the
     * Alpine editor receives consistent booleans and numbers.
     *
     * @param  mixed  $options
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeOptions($options): array
    {
        return collect(is_array($options) ? $options : [])
            ->map(fn ($option) => [
                'name' => (string) ($option['name'] ?? ''),
                'type' => in_array($option['type'] ?? 'single', ['single', 'multiple'], true)
                    ? $option['type']
                    : 'single',
                'is_required' => filter_var($option['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'values' => collect($option['values'] ?? [])
                    ->map(fn ($value) => [
                        'value' => (string) ($value['value'] ?? ''),
                        'price_delta' => (float) ($value['price_delta'] ?? 0),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /** @param array<int, array<string, mixed>> $options */
    protected function syncOptions(Product $product, array $options): void
    {
        $product->options()->delete();

        foreach (array_values($options) as $optionIndex => $option) {
            $created = $product->options()->create([
                'name' => $option['name'],
                'type' => $option['type'] ?? 'single',
                'is_required' => (bool) ($option['is_required'] ?? false),
                'sort_order' => $optionIndex,
            ]);

            foreach (array_values($option['values'] ?? []) as $valueIndex => $value) {
                $created->values()->create([
                    'value' => $value['value'],
                    'price_delta_fils' => Money::jod($value['price_delta'] ?? 0),
                    'sort_order' => $valueIndex,
                ]);
            }
        }
    }

    protected function resolveImage(Request $request, ?Product $product = null): ?string
    {
        if ($request->hasFile('image_file')) {
            return $request->file('image_file')->store('products', 'public');
        }

        if ($request->boolean('remove_image')) {
            return null;
        }

        $url = trim($request->input('image_url', ''));

        if ($url !== '') {
            return $url;
        }

        return $product?->image_path;
    }

    protected function uniqueSlug(string $candidate, ?Product $ignore = null): string
    {
        $base = Str::slug($candidate);

        if ($base === '') {
            $base = 'product-'.Str::lower(Str::random(8));
        }

        $slug = $base;
        $counter = 2;

        while (Product::withTrashed()
            ->where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function currentStore(): Store
    {
        return auth()->user()->store()->firstOrFail();
    }

    protected function authorizeProduct(Product $product): Store
    {
        $store = $this->currentStore();

        abort_unless($product->store_id === $store->id, 404);

        return $store;
    }

    /** @param array<int, string> $allowed */
    protected function inList(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
