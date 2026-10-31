<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\DepositStatus;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Services\OrderService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $store = $this->store();
        $view = $request->string('view')->toString() === 'board' ? 'board' : 'list';

        $filters = [
            'status' => $this->safeStatus($request->string('status')->toString()),
            'date' => $this->inList($request->string('date')->toString(), ['all', 'today', 'upcoming', 'late', 'week'], 'all'),
            'fulfillment' => $this->inList($request->string('fulfillment')->toString(), ['all', 'delivery', 'pickup'], 'all'),
            'payment' => $this->inList($request->string('payment')->toString(), ['all', 'cash', 'cliq', 'bank_transfer'], 'all'),
            'sort' => $this->inList($request->string('sort')->toString(), ['schedule', 'recent', 'total'], 'schedule'),
            'q' => trim($request->string('q')->toString()),
        ];

        // The board always shows every workflow column, so it ignores the status filter.
        $queryFilters = $filters;
        $queryFilters['status'] = $view === 'board' ? 'all' : $filters['status'];

        $query = $this->buildQuery($store, $queryFilters);

        $orders = $view === 'board'
            ? $query->limit(240)->get()
            : $query->paginate(12)->withQueryString();

        $statusCounts = $this->statusCounts($store);

        return view('dashboard.orders.index', [
            'store' => $store,
            'view' => $view,
            'filters' => $filters,
            'orders' => $orders,
            'statusCounts' => $statusCounts,
            'stats' => $this->stats($store, $statusCounts['active'] ?? 0),
        ]);
    }

    public function show(Order $order, WhatsAppService $whatsapp): View
    {
        $this->authorizeOrder($order);
        $store = $this->store();

        $order->load(['customer', 'items.product', 'events.user', 'messages']);

        $steps = [OrderStatus::New, OrderStatus::Confirmed, OrderStatus::InProduction, OrderStatus::Ready];

        if ($order->fulfillment_type === FulfillmentType::Delivery) {
            $steps[] = OrderStatus::OutForDelivery;
        }

        $steps[] = OrderStatus::Delivered;

        $eventTimes = [OrderStatus::New->value => $order->created_at];

        foreach ($order->events as $event) {
            $to = $event->payload['to_value'] ?? null;

            if ($event->type === 'status_changed' && $to) {
                $eventTimes[$to] = $event->created_at;
            }
        }

        $templates = collect(WhatsAppService::TEMPLATES)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'url' => $whatsapp->link($order, $key),
            'summary' => $whatsapp->summary($order, $key),
        ])->values();

        return view('dashboard.orders.show', [
            'store' => $store,
            'order' => $order,
            'steps' => $steps,
            'eventTimes' => $eventTimes,
            'templates' => $templates,
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($order);

        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $to = OrderStatus::from($data['status']);

        $orders->transition($order, $to, $request->user());

        return back()->with('status', 'تم تحديث حالة الطلب إلى «'.$to->label().'».');
    }

    public function updateDeposit(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->deposit_fils <= 0) {
            return back()->with('status', 'هذا الطلب لا يتضمن عربوناً.');
        }

        $data = $request->validate([
            'deposit_status' => ['required', Rule::enum(DepositStatus::class)],
        ]);

        $status = DepositStatus::from($data['deposit_status']);

        $orders->updateDeposit($order, $status, $request->user());

        return back()->with('status', 'تم تحديث العربون: '.$status->label().'.');
    }

    public function updateNotes(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($order);

        $data = $request->validate([
            'internal_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $orders->addNote($order, $data['internal_notes'] ?? null, $request->user());

        return back()->with('status', 'تم حفظ الملاحظة الداخلية.');
    }

    public function storeMessage(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $this->authorizeOrder($order);

        $data = $request->validate([
            'template' => ['required', Rule::in(array_keys(WhatsAppService::TEMPLATES))],
        ]);

        $log = $orders->logMessage($order, $data['template'], $request->user());

        return back()->with('status', 'تم تسجيل رسالة «'.$log->templateLabel().'».');
    }

    protected function buildQuery(Store $store, array $filters): Builder
    {
        $query = Order::forStore($store)->with(['customer', 'items']);

        switch ($filters['status']) {
            case 'all':
                break;
            case 'active':
                $query->whereNotIn('status', [OrderStatus::Delivered, OrderStatus::Cancelled]);
                break;
            default:
                $query->where('status', $filters['status']);
        }

        $query->search($filters['q'] ?: null);

        if ($filters['fulfillment'] !== 'all') {
            $query->where('fulfillment_type', $filters['fulfillment']);
        }

        if ($filters['payment'] !== 'all') {
            $query->where('payment_method', $filters['payment']);
        }

        switch ($filters['date']) {
            case 'today':
                $query->whereDate('scheduled_at', today())->where('status', '!=', OrderStatus::Cancelled);
                break;
            case 'upcoming':
                $query->where('scheduled_at', '>=', now())->whereNotIn('status', [OrderStatus::Delivered, OrderStatus::Cancelled]);
                break;
            case 'late':
                $query->where('scheduled_at', '<', now())->whereNotIn('status', [OrderStatus::Delivered, OrderStatus::Cancelled]);
                break;
            case 'week':
                $query->whereBetween('scheduled_at', [now()->startOfWeek(Carbon::SUNDAY), now()->endOfWeek(Carbon::SATURDAY)]);
                break;
        }

        return match ($filters['sort']) {
            'recent' => $query->latest('created_at'),
            'total' => $query->orderByDesc('total_fils'),
            default => $query->orderBy('scheduled_at')->orderByDesc('id'),
        };
    }

    /** @return array<string, int> */
    protected function statusCounts(Store $store): array
    {
        $counts = Order::forStore($store)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();

        $counts['all'] = array_sum($counts);

        $counts['active'] = array_sum(array_intersect_key($counts, array_flip([
            OrderStatus::New->value,
            OrderStatus::Confirmed->value,
            OrderStatus::InProduction->value,
            OrderStatus::Ready->value,
            OrderStatus::OutForDelivery->value,
        ])));

        return $counts;
    }

    /** @return array<string, int> */
    protected function stats(Store $store, int $active): array
    {
        return [
            'active' => $active,
            'today' => Order::forStore($store)->whereDate('scheduled_at', today())
                ->where('status', '!=', OrderStatus::Cancelled)->count(),
            'late' => Order::forStore($store)->where('scheduled_at', '<', now())
                ->whereNotIn('status', [OrderStatus::Delivered, OrderStatus::Cancelled])->count(),
            'deposits' => (int) Order::forStore($store)
                ->where('deposit_status', DepositStatus::Pending)
                ->where('status', '!=', OrderStatus::Cancelled)
                ->sum('deposit_fils'),
        ];
    }

    protected function store(): Store
    {
        return auth()->user()->store()->firstOrFail();
    }

    protected function authorizeOrder(Order $order): void
    {
        abort_unless($order->store_id === $this->store()->id, 404);
    }

    protected function safeStatus(string $status): string
    {
        $allowed = array_merge(array_column(OrderStatus::cases(), 'value'), ['all', 'active']);

        return in_array($status, $allowed, true) ? $status : 'all';
    }

    /** @param array<int, string> $allowed */
    protected function inList(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
