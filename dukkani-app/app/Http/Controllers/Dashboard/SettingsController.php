<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Store;
use App\Models\StoreHour;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $store = $this->store();

        $hours = collect(StoreHour::DAYS)->map(fn ($label, $day) => $store->hours->firstWhere('day_of_week', $day)
            ?? new StoreHour([
                'store_id' => $store->id,
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '20:00',
                'is_closed' => false,
            ]))->values();

        return view('dashboard.settings.index', [
            'store' => $store,
            'hours' => $hours,
            'blockedDates' => $store->blockedDates()->get(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $store = $this->store();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'slug' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'min:7', 'max:30', 'regex:/^[0-9+\s\-()]+$/'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\s\-()]+$/'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:200'],
            'logo_url' => ['nullable', 'string', 'max:600'],
            'cover_url' => ['nullable', 'string', 'max:600'],
            'logo_file' => ['nullable', 'image', 'max:2048'],
            'cover_file' => ['nullable', 'image', 'max:4096'],
        ], [
            'name.required' => 'اسم المتجر مطلوب.',
            'slug.required' => 'رابط المتجر مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف يحتوي رموزاً غير مسموحة.',
            'whatsapp.regex' => 'رقم الواتساب يحتوي رموزاً غير مسموحة.',
            'city.required' => 'المدينة مطلوبة.',
            'logo_file.image' => 'الشعار يجب أن يكون صورة.',
            'logo_file.max' => 'حجم الشعار يجب ألا يتجاوز 2 ميغابايت.',
            'cover_file.image' => 'صورة الغلاف يجب أن تكون صورة.',
            'cover_file.max' => 'حجم صورة الغلاف يجب ألا يتجاوز 4 ميغابايت.',
        ]);

        $slug = Str::slug($data['slug']);

        if ($slug === '') {
            throw ValidationException::withMessages(['slug' => 'استخدمي أحرفاً إنجليزية أو أرقاماً في رابط المتجر.']);
        }

        $taken = Store::where('slug', $slug)->whereKeyNot($store->id)->exists();

        if ($taken) {
            throw ValidationException::withMessages(['slug' => 'هذا الرابط مستخدم لمتجر آخر.']);
        }

        $store->update([
            'name' => $data['name'],
            'slug' => $slug,
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'phone' => trim($data['phone']),
            'whatsapp' => filled($data['whatsapp'] ?? null) ? trim($data['whatsapp']) : null,
            'instagram' => $data['instagram'] ?? null,
            'city' => $data['city'],
            'address' => $data['address'] ?? null,
            'logo_path' => $this->resolveAsset($request, 'logo', $store),
            'cover_path' => $this->resolveAsset($request, 'cover', $store),
        ]);

        return redirect()
            ->route('settings.index', ['tab' => 'profile'])
            ->with('status', 'تم حفظ معلومات المتجر.');
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $store = $this->store();

        $data = $request->validate([
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:100'],
            'deposit_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'lead_time_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'deposit_instructions' => ['nullable', 'string', 'max:1000'],
        ], [
            'delivery_fee.required' => 'أدخلي رسوم التوصيل (0 إن كان مجانياً).',
            'delivery_fee.numeric' => 'رسوم التوصيل يجب أن تكون رقماً.',
            'deposit_percent.required' => 'حددي نسبة العربون.',
            'deposit_percent.min' => 'نسبة العربون لا يمكن أن تكون سالبة.',
            'deposit_percent.max' => 'نسبة العربون لا يمكن أن تتجاوز 100٪.',
            'lead_time_hours.required' => 'حددي أقل مدة تحضير.',
            'lead_time_hours.min' => 'أقل مدة تحضير هي ساعة واحدة.',
        ]);

        $pickup = $request->boolean('pickup_enabled');
        $delivery = $request->boolean('delivery_enabled');

        if (! $pickup && ! $delivery) {
            throw ValidationException::withMessages([
                'delivery_enabled' => 'اختاري الاستلام من المتجر أو التوصيل على الأقل.',
            ]);
        }

        $store->update([
            'pickup_enabled' => $pickup,
            'delivery_enabled' => $delivery,
            'delivery_fee_fils' => Money::jod($data['delivery_fee']),
            'deposit_percent' => (int) $data['deposit_percent'],
            'lead_time_hours' => (int) $data['lead_time_hours'],
            'deposit_instructions' => $data['deposit_instructions'] ?? null,
        ]);

        return redirect()
            ->route('settings.index', ['tab' => 'delivery'])
            ->with('status', 'تم حفظ إعدادات التوصيل والدفع.');
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $store = $this->store();

        $data = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
        ], [
            'hours.*.open_time.date_format' => 'صيغة وقت الفتح غير صحيحة.',
            'hours.*.close_time.date_format' => 'صيغة وقت الإغلاق غير صحيحة.',
        ]);

        foreach (range(0, 6) as $day) {
            $row = $data['hours'][$day] ?? [];
            $closed = $request->boolean("hours.{$day}.is_closed");
            $open = $closed ? null : ($row['open_time'] ?? null);
            $close = $closed ? null : ($row['close_time'] ?? null);

            if (! $closed && (! $open || ! $close)) {
                throw ValidationException::withMessages([
                    "hours.{$day}.open_time" => 'حددي وقت الفتح والإغلاق، أو علّمي اليوم كمغلق.',
                ]);
            }

            StoreHour::updateOrCreate(
                ['store_id' => $store->id, 'day_of_week' => $day],
                ['open_time' => $open, 'close_time' => $close, 'is_closed' => $closed],
            );
        }

        $store->unsetRelation('hours');

        return redirect()
            ->route('settings.index', ['tab' => 'hours'])
            ->with('status', 'تم حفظ ساعات العمل.');
    }

    public function storeBlockedDate(Request $request): RedirectResponse
    {
        $store = $this->store();

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:120'],
        ], [
            'date.required' => 'اختاري التاريخ.',
            'date.date_format' => 'صيغة التاريخ غير صحيحة.',
            'date.after_or_equal' => 'اختاري تاريخاً اليوم أو بعده.',
        ]);

        $existing = $store->blockedDates()->whereDate('date', $data['date'])->exists();

        if ($existing) {
            throw ValidationException::withMessages(['date' => 'هذا التاريخ محظور مسبقاً.']);
        }

        $store->blockedDates()->create([
            'date' => $data['date'],
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()
            ->route('settings.index', ['tab' => 'hours'])
            ->with('status', 'تم حظر '.$data['date'].'.');
    }

    public function destroyBlockedDate(BlockedDate $blockedDate): RedirectResponse
    {
        $store = $this->store();

        abort_unless($blockedDate->store_id === $store->id, 404);

        $blockedDate->delete();

        return redirect()
            ->route('settings.index', ['tab' => 'hours'])
            ->with('status', 'تم إلغاء حظر التاريخ.');
    }

    protected function resolveAsset(Request $request, string $key, Store $store): ?string
    {
        if ($request->hasFile($key.'_file')) {
            return $request->file($key.'_file')->store('store', 'public');
        }

        if ($request->boolean('remove_'.$key)) {
            return null;
        }

        $url = trim($request->input($key.'_url', ''));

        if ($url !== '') {
            return $url;
        }

        return $store->{$key.'_path'};
    }

    protected function store(): Store
    {
        return auth()->user()->store()->firstOrFail();
    }
}
