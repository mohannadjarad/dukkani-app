<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $store = $this->store();

        $filters = [
            'q' => trim($request->string('q')->toString()),
            'tag' => trim($request->string('tag')->toString()),
            'sort' => $this->inList($request->string('sort')->toString(), ['recent', 'spent', 'orders', 'name'], 'recent'),
        ];

        $query = Customer::where('store_id', $store->id)
            ->withCount('orders')
            ->withSum(['orders as spent_fils' => fn ($q) => $q->where('status', 'delivered')], 'total_fils')
            ->withMax('orders as last_order_at', 'created_at');

        if ($filters['q'] !== '') {
            $query->search($filters['q']);
        }

        if ($filters['tag'] !== '') {
            $query->whereJsonContains('tags', $filters['tag']);
        }

        match ($filters['sort']) {
            'spent' => $query->orderByDesc('spent_fils'),
            'orders' => $query->orderByDesc('orders_count'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('last_order_at')->orderByDesc('id'),
        };

        $customers = $query->paginate(15)->withQueryString();

        $allTags = $store->customers()
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $stats = [
            'total' => $store->customers()->count(),
            'repeat' => $store->customers()->has('orders', '>', 1)->count(),
            'vip' => $store->customers()->whereJsonContains('tags', 'vip')->count(),
            'spent' => (int) Order::forStore($store)->where('status', 'delivered')->sum('total_fils'),
        ];

        return view('dashboard.customers.index', [
            'store' => $store,
            'customers' => $customers,
            'allTags' => $allTags,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function show(Customer $customer): View
    {
        $store = $this->authorizeCustomer($customer);

        $customer->load(['orders' => fn ($q) => $q->with('items')->latest('scheduled_at')]);

        $orders = $customer->orders;
        $delivered = $orders->where('status', 'delivered');
        $spent = (int) $delivered->sum('total_fils');
        $deliveredCount = $delivered->count();

        $stats = [
            'orders' => $orders->count(),
            'delivered' => $deliveredCount,
            'spent' => $spent,
            'average' => $deliveredCount > 0 ? (int) round($spent / $deliveredCount) : 0,
            'first' => $orders->min('created_at'),
            'last' => $orders->max('created_at'),
        ];

        $allTags = $store->customers()
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('dashboard.customers.show', [
            'store' => $store,
            'customer' => $customer,
            'orders' => $orders,
            'stats' => $stats,
            'allTags' => $allTags,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'phone' => [
                'required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+\s\-()]+$/',
                Rule::unique('customers', 'phone')->where('store_id', $customer->store_id)->ignore($customer->id),
            ],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:300'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'string', 'max:300'],
        ], [
            'name.required' => 'اسم العميل مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف يحتوي رموزاً غير مسموحة.',
            'phone.unique' => 'يوجد عميل آخر بهذا الرقم.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        $customer->update([
            'name' => $data['name'],
            'phone' => trim($data['phone']),
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'tags' => $this->parseTags($data['tags'] ?? ''),
        ]);

        return back()->with('status', 'تم تحديث بيانات '.$customer->name.'.');
    }

    /** @return array<int, string> */
    protected function parseTags(string $tags): array
    {
        return collect(preg_split('/[,،]+/u', $tags))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->take(12)
            ->values()
            ->all();
    }

    protected function store(): Store
    {
        return auth()->user()->store()->firstOrFail();
    }

    protected function authorizeCustomer(Customer $customer): Store
    {
        $store = $this->store();

        abort_unless($customer->store_id === $store->id, 404);

        return $store;
    }

    /** @param array<int, string> $allowed */
    protected function inList(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
