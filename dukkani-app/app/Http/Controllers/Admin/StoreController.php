<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(private readonly PlanService $plans) {}

    public function index(): View
    {
        $stores = Store::with('owner')->orderByDesc('id')->get();

        $rows = $stores->map(fn (Store $store) => [
            'store' => $store,
            'owner' => $store->owner,
            'plan' => $this->plans->effectivePlan($store),
            'label' => $this->plans->label($store),
            'unlimited' => $this->plans->isUnlimited($store),
            'trial' => $this->plans->isOnTrial($store),
            'orders' => $this->plans->monthlyOrders($store),
            'limit' => $this->plans->orderLimit($store),
            'override' => (int) ($store->order_limit_override ?? 0),
            'expires_at' => $store->plan_expires_at,
            'active' => $store->status === 'active',
        ]);

        return view('admin.stores.index', [
            'rows' => $rows,
            'plans' => $this->plans->plans(),
            'summary' => [
                'stores' => $stores->count(),
                'trial' => $rows->where('trial', true)->count(),
                'paid' => $rows->where('unlimited', true)->where('trial', false)->count(),
                'suspended' => $rows->where('active', false)->count(),
            ],
        ]);
    }

    public function updatePlan(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in($this->plans->keys())],
            'months' => ['nullable', 'integer', Rule::in([1, 3, 12])],
        ], [
            'plan.required' => 'اختاري الخطة.',
            'plan.in' => 'الخطة غير معروفة.',
            'months.in' => 'المدة يجب أن تكون 1 أو 3 أو 12 شهراً.',
        ]);

        if ($data['plan'] === 'free') {
            $this->plans->activate($store, 'free');
        } else {
            $this->plans->activate($store, $data['plan'], (int) ($data['months'] ?? 1));
        }

        return back()->with('status', 'تم تحديث خطة '.$store->name.'.');
    }

    public function updateOverride(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'order_limit_override' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ], [
            'order_limit_override.integer' => 'الحد الإضافي يجب أن يكون رقماً.',
            'order_limit_override.min' => 'الحد الإضافي لا يمكن أن يكون سالباً.',
        ]);

        $this->plans->grantOrderOverride($store, isset($data['order_limit_override']) ? (int) $data['order_limit_override'] : null);

        return back()->with('status', 'تم تحديث الحد الإضافي لـ '.$store->name.'.');
    }

    public function suspend(Store $store): RedirectResponse
    {
        $store->forceFill(['status' => 'suspended'])->save();

        return back()->with('status', 'تم تعليق '.$store->name.' — المتجر مخفي عن العملاء.');
    }

    public function activate(Store $store): RedirectResponse
    {
        $store->forceFill(['status' => 'active'])->save();

        return back()->with('status', 'تم تفعيل '.$store->name.'.');
    }
}
