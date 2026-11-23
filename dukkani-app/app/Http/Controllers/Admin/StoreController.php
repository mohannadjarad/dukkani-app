<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\PlanService;
use App\Services\StoreProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(
        private readonly PlanService $plans,
        private readonly StoreProvisioner $provisioner,
    ) {}

    public function index(): View
    {
        $stores = Store::with('owner')->orderByDesc('id')->get();

        $rows = $stores->map(fn (Store $store) => [
            'store' => $store,
            'owner' => $store->owner,
            'plan' => $this->plans->effectivePlan($store),
            'stored_plan' => $this->plans->storedPlan($store),
            'label' => $this->plans->label($store),
            'unlimited' => $this->plans->isUnlimited($store),
            'trial' => $this->plans->isOnTrial($store),
            'trial_flag' => (bool) $store->is_trial,
            'expired' => $store->plan_expires_at !== null
                && $store->plan_expires_at->isPast()
                && ! $this->plans->isUnlimited($store),
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

    public function create(): View
    {
        return view('admin.stores.create', [
            'countries' => (array) config('app.countries', []),
            'trialDays' => (int) config('plans.trial_days', 3),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'slug' => Str::slug((string) $request->input('slug')),
        ]);

        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:stores,slug'],
            'owner_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', Rule::in(array_keys((array) config('app.countries', [])))],
        ], [
            'store_name.required' => 'اسم المتجر مطلوب.',
            'slug.required' => 'رابط المتجر مطلوب بحروف إنجليزية وأرقام وشرطات.',
            'slug.regex' => 'الرابط يقبل الحروف الإنجليزية الصغيرة والأرقام والشرطات فقط.',
            'slug.unique' => 'هذا الرابط مستخدم لمتجر آخر.',
            'owner_name.required' => 'اسم المالك مطلوب.',
            'email.required' => 'بريد المالك مطلوب.',
            'email.email' => 'بريد المالك غير صحيح.',
            'email.unique' => 'هذا البريد مستخدم لحساب آخر.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'city.required' => 'المدينة مطلوبة.',
            'country_code.in' => 'اختاري دولة من القائمة.',
        ]);

        $country = (array) config("app.countries.{$data['country_code']}", []);
        $data['currency'] = (string) ($country['currency'] ?? 'JOD');
        $data['timezone'] = (string) ($country['timezone'] ?? 'Asia/Amman');
        $data['whatsapp'] = filled($data['whatsapp'] ?? null) ? $data['whatsapp'] : null;

        [$user, $store] = $this->provisioner->create($data, $request->boolean('start_trial'));

        return redirect()
            ->route('admin.stores.index')
            ->with('status', 'تم إنشاء متجر '.$store->name.' — دخول المالك: '.$user->email.'.');
    }

    public function updatePlan(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in($this->plans->purchasableKeys())],
            'months' => ['nullable', 'integer', Rule::in([1, 3, 12])],
            'ends_at' => ['nullable', 'date'],
            'is_trial' => ['nullable', 'boolean'],
        ], [
            'plan.required' => 'اختاري الخطة.',
            'plan.in' => 'الخطة غير متاحة للبيع حالياً.',
            'months.in' => 'المدة يجب أن تكون 1 أو 3 أو 12 شهراً.',
            'ends_at.date' => 'تاريخ الانتهاء غير صحيح.',
        ]);

        $plan = $data['plan'];
        $trial = $request->boolean('is_trial') && $plan !== 'free';
        $months = filled($data['months'] ?? null) ? (int) $data['months'] : null;
        $endsAt = filled($data['ends_at'] ?? null)
            ? Carbon::parse($data['ends_at'])->endOfDay()
            : null;

        if ($plan === 'free') {
            $this->plans->activate($store, 'free');

            return back()->with('status', 'تم تحويل '.$store->name.' إلى الخطة المجانية.');
        }

        if ($endsAt !== null && $endsAt->isPast()) {
            throw ValidationException::withMessages([
                'ends_at' => 'تاريخ الانتهاء يجب أن يكون اليوم أو بعده.',
            ]);
        }

        if ($endsAt !== null) {
            // The admin picked the exact day the plan should end.
            $this->plans->setPlan($store, $plan, $endsAt, $trial);
        } elseif ($months !== null) {
            if ($trial) {
                $this->plans->setPlan($store, $plan, now()->addMonths($months)->endOfDay(), true);
            } else {
                $this->plans->activate($store, $plan, $months);
            }
        } else {
            if ($trial) {
                throw ValidationException::withMessages([
                    'ends_at' => 'فترة التجربة تحتاج تاريخ انتهاء.',
                ]);
            }

            $this->plans->activate($store, $plan);
        }

        $store->refresh();

        $status = $trial
            ? 'تم تفعيل تجربة '.$this->plans->label($store).' لـ '.$store->name
            : 'تم تفعيل خطة '.$this->plans->label($store).' لـ '.$store->name;

        $status .= $store->plan_expires_at !== null
            ? ' حتى '.$store->plan_expires_at->translatedFormat('j F Y').'.'
            : ' بلا تاريخ انتهاء.';

        return back()->with('status', $status);
    }

    public function extend(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'extend_months' => ['required', 'integer', Rule::in([1, 3, 6, 12])],
        ], [
            'extend_months.required' => 'اختاري مدة التمديد.',
            'extend_months.in' => 'مدة التمديد يجب أن تكون 1 أو 3 أو 6 أو 12 شهراً.',
        ]);

        if ($this->plans->storedPlan($store) === 'free') {
            return back()->withErrors([
                'extend_months' => 'المتجر على الخطة المجانية — فعّلي خطة مدفوعة أو تجربة أولاً.',
            ]);
        }

        if ($store->plan_expires_at === null) {
            return back()->withErrors([
                'extend_months' => 'هذه الخطة بلا تاريخ انتهاء، فلا حاجة للتمديد.',
            ]);
        }

        $this->plans->extend($store, (int) $data['extend_months']);
        $store->refresh();

        return back()->with('status', 'تم تمديد '.($store->is_trial ? 'التجربة' : 'الخطة').' حتى '
            .$store->plan_expires_at->translatedFormat('j F Y').'.');
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
