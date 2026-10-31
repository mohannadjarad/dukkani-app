<?php

namespace Database\Seeders;

use App\Enums\DepositStatus;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\BlockedDate;
use App\Models\Category;
use App\Models\Customer;
use App\Models\MessageLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreHour;
use App\Models\StoreNotification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::updateOrCreate(
            ['email' => 'demo@dukkani.jo'],
            ['name' => 'أمل العبادي', 'password' => 'demo1234', 'email_verified_at' => now()]
        );

        $store = Store::updateOrCreate(
            ['slug' => 'amal-bakes'],
            [
                'owner_id' => $owner->id,
                'name' => 'أمل بيكنز',
                'tagline' => 'حلويات منزلية تُخبز على مهل، لكل مناسبة تحبها.',
                'description' => "بدأت أمل بيكنز من مطبخ صغير في جبل عمّان، بوصفة كيكة عسل ورثتها عن جدتها. اليوم نُخبز كل طلب بعد تأكيده، بمكونات طازجة وزبدة حقيقية وشوكولاتة بلجيكية، ونغلّفه بعناية توصلكم كأنه هدية.\n\nنؤمن إن الحلويات مش بس طعم — هي رسالة، وذكرى، وطريقة نقول بها «بحبك» أو «شكراً». لهذا نعطيكم مساحة تختارون التفاصيل، ونتولى نحن الباقي.",
                'cover_path' => 'images/amal-bakes/store-cover.webp',
                'phone' => '079 000 0000',
                'whatsapp' => '962790000000',
                'instagram' => 'amalbakes.jo',
                'city' => 'عمّان',
                'address' => 'شارع الرينبو ١٤، جبل عمّان',
                'currency' => 'JOD',
                'delivery_fee_fils' => 2000,
                'deposit_percent' => 30,
                'pickup_enabled' => true,
                'delivery_enabled' => true,
                'lead_time_hours' => 48,
                'deposit_instructions' => 'حوّلي العربون عبر CliQ إلى 0790000000 وأرسلي صورة التحويل على واتساب. نبدأ التحضير فور تأكيد التحويل.',
                'timezone' => 'Asia/Amman',
                'status' => 'active',
                'onboarding_completed_at' => now(),
            ]
        );

        $hours = [
            0 => ['10:00', '20:00', false],
            1 => ['10:00', '20:00', false],
            2 => ['10:00', '20:00', false],
            3 => ['10:00', '20:00', false],
            4 => ['10:00', '20:00', false],
            5 => [null, null, true],
            6 => ['11:00', '21:00', false],
        ];

        foreach ($hours as $day => [$open, $close, $closed]) {
            StoreHour::updateOrCreate(
                ['store_id' => $store->id, 'day_of_week' => $day],
                ['open_time' => $open, 'close_time' => $close, 'is_closed' => $closed]
            );
        }

        BlockedDate::where('store_id', $store->id)->delete();
        BlockedDate::create(['store_id' => $store->id, 'date' => now()->addDays(9)->toDateString(), 'reason' => 'مغلق لطلب مناسبة خاصة']);
        BlockedDate::create(['store_id' => $store->id, 'date' => now()->addDays(23)->toDateString(), 'reason' => 'صيانة المطبخ']);

        $categories = collect([
            ['name' => 'كيك ومناسبات', 'sort_order' => 1],
            ['name' => 'معجنات وبراونيز', 'sort_order' => 2],
            ['name' => 'بسكويت وكوكيز', 'sort_order' => 3],
            ['name' => 'بوكسات هدايا', 'sort_order' => 4],
        ])->map(fn ($data) => Category::updateOrCreate(
            ['store_id' => $store->id, 'name' => $data['name']],
            $data + ['is_active' => true]
        ));

        $productData = [
            [
                'name' => 'كيكة العسل واللوز',
                'slug' => 'honey-almond-cake',
                'category' => 0,
                'price' => 18500,
                'lead' => 48,
                'featured' => true,
                'image' => 'images/amal-bakes/honey-almond-cake.webp',
                'description' => 'طبقات هشّة من كيك العسل بوصفة الجدة، محشوة بكريمة اللوز ومغطاة بالعسل الطبيعي واللوز المحمص. اختر الحجم المناسب لمناسبتك.',
                'options' => [
                    ['الحجم', true, [['وسط (6–8 أشخاص)', 0], ['كبير (10–12 شخص)', 6000]]],
                    ['إضافة لوز محمص', false, [['بدون', 0], ['مع لوز إضافي', 1500]]],
                ],
            ],
            [
                'name' => 'تشيز كيك التوت',
                'slug' => 'berry-cheesecake',
                'category' => 0,
                'price' => 16000,
                'lead' => 48,
                'featured' => true,
                'image' => 'images/amal-bakes/berry-cheesecake.webp',
                'description' => 'قاعدة بسكويت بالزبدة، طبقة تشيز كيك كريمية، وتوت طازج مع صوص التوت المنزلي. تُحضّر يوم التسليم لتوصل بأفضل قوام.',
                'options' => [
                    ['الإضافة', false, [['بدون', 0], ['توت طازج إضافي', 1500], ['صوص شوكولاتة', 1000]]],
                ],
            ],
            [
                'name' => 'كيكة الشوكولاتة الفادج',
                'slug' => 'chocolate-fudge-cake',
                'category' => 0,
                'price' => 22000,
                'lead' => 72,
                'featured' => true,
                'image' => 'images/amal-bakes/chocolate-fudge-cake.webp',
                'description' => 'شوكولاتة بلجيكية داكنة، طبقات فادج غنية، ورشّة ملح البحر. خيار آمن لكل عاشق شوكولاتة. متوفرة بحجمين.',
                'options' => [
                    ['الحجم', true, [['6 أشخاص', 0], ['10 أشخاص', 7000]]],
                    ['كتابة على الكيك', false, [['بدون', 0], ['اسم أو عبارة قصيرة', 1000]]],
                ],
            ],
            [
                'name' => 'كرواسون الزبدة',
                'slug' => 'butter-croissant',
                'category' => 1,
                'price' => 1750,
                'lead' => 24,
                'featured' => false,
                'image' => 'images/amal-bakes/butter-croissant.webp',
                'description' => 'كرواسون فرنسي بطبقات مقرمشة وزبدة حقيقية، يُخبز طازجاً كل صباح. يمكن اختيار حشوة اللوز.',
                'options' => [
                    ['النوع', true, [['زبدة كلاسيك', 0], ['حشوة اللوز', 500]]],
                ],
            ],
            [
                'name' => 'براوني الفستق',
                'slug' => 'pistachio-brownie',
                'category' => 1,
                'price' => 2500,
                'lead' => 24,
                'featured' => true,
                'image' => 'images/amal-bakes/pistachio-brownie.webp',
                'description' => 'براوني طري غني بالشوكولاتة، مغطى بكريمة الفستق الحلبي المطحون. يُقدّم دافئاً أو بدرجة حرارة الغرفة.',
                'options' => [
                    ['الإضافة', false, [['بدون', 0], ['فستق حلبي إضافي', 1000]]],
                ],
            ],
            [
                'name' => 'سينامون رول',
                'slug' => 'cinnamon-roll',
                'category' => 1,
                'price' => 2250,
                'lead' => 24,
                'featured' => false,
                'image' => 'images/amal-bakes/cinnamon-roll.webp',
                'description' => 'عجينة ناعمة ملفوفة بالقرفة والسكر البني، مع صوص كريم الجبن. الأفضل مع قهوة الصباح.',
                'options' => [],
            ],
            [
                'name' => 'كوكيز الشوكولاتة المملحة',
                'slug' => 'salted-chocolate-cookies',
                'category' => 2,
                'price' => 8500,
                'lead' => 24,
                'featured' => true,
                'image' => 'images/amal-bakes/salted-chocolate-cookies.webp',
                'description' => 'علبة 6 حبات من الكوكيز الطري بقطع الشوكولاتة البلجيكية ورشة ملح البحر. مثالية للضيافة أو الهدايا.',
                'options' => [
                    ['النوع', false, [['شوكولاتة كلاسيك', 0], ['شوكولاتة مزدوجة', 1000]]],
                ],
            ],
            [
                'name' => 'بوكس الضيافة الصغير',
                'slug' => 'small-hospitality-box',
                'category' => 3,
                'price' => 35000,
                'lead' => 72,
                'featured' => true,
                'image' => 'images/amal-bakes/small-hospitality-box.webp',
                'description' => 'تشكيلة مختارة من الكيك المقطّع والكوكيز والبراوني، مع بطاقة إهداء وتغليف أنيق. هدية جاهزة لضيوفك أو فريق عملك.',
                'options' => [
                    ['الحجم', true, [['صغير (4–6 أشخاص)', 0], ['كبير (8–12 شخص)', 15000]]],
                    ['بطاقة إهداء', false, [['بدون', 0], ['مع بطاقة مكتوبة', 500]]],
                ],
            ],
            [
                'name' => 'بوكس معجنات مشكلة',
                'slug' => 'assorted-pastry-box',
                'category' => 3,
                'price' => 12000,
                'lead' => 48,
                'featured' => false,
                'image' => 'images/amal-bakes/assorted-pastry-box.webp',
                'description' => '12 قطعة مشكلة من الكرواسون والسينامون رول والبراوني المقطّع، مناسبة لاجتماعات الصباح والضيافة السريعة.',
                'options' => [],
            ],
            [
                'name' => 'كيكة الورد والليمون',
                'slug' => 'rose-lemon-cake',
                'category' => 0,
                'price' => 20000,
                'lead' => 48,
                'featured' => false,
                'image' => 'images/amal-bakes/rose-lemon-cake.webp',
                'description' => 'كيك ليمون خفيف بكريمة الورد وماء الورد، مزين بورد مجفف صالح للأكل. خفيف ومنعش لحفلات ما بعد الظهر.',
                'options' => [
                    ['الحجم', true, [['وسط (6–8 أشخاص)', 0], ['كبير (10–12 شخص)', 6000]]],
                ],
            ],
        ];

        $products = collect($productData)->map(function ($data) use ($store, $categories) {
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'store_id' => $store->id,
                    'category_id' => $categories[$data['category']]->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_fils' => $data['price'],
                    'image_path' => $data['image'],
                    'lead_time_hours' => $data['lead'],
                    'is_active' => true,
                    'is_featured' => $data['featured'],
                    'sort_order' => $data['price'],
                ]
            );

            $product->options()->delete();

            foreach ($data['options'] as $optionIndex => [$name, $required, $values]) {
                $option = $product->options()->create([
                    'name' => $name,
                    'type' => 'single',
                    'is_required' => $required,
                    'sort_order' => $optionIndex,
                ]);

                foreach ($values as $valueIndex => [$value, $delta]) {
                    $option->values()->create([
                        'value' => $value,
                        'price_delta_fils' => $delta,
                        'sort_order' => $valueIndex,
                    ]);
                }
            }

            return $product->load('options.values');
        })->keyBy('slug');

        $customers = collect([
            ['نور الخالدي', '0791111001', 'عبدون', ['vip']],
            ['لينا حداد', '0791111002', 'الرابية', ['متكرر']],
            ['رامي العبادي', '0791111003', 'جبل عمّان', []],
            ['سارة أبو زيد', '0791111004', 'دابوق', ['vip']],
            ['مايا النجار', '0791111005', 'الصويفية', ['شركات']],
            ['هبة القيسي', '0791111006', 'خلدا', []],
            ['عمر الشريف', '0791111007', 'تلاع العلي', ['متكرر']],
            ['دانا مراد', '0791111008', 'الجبيهة', []],
            ['ياسمين عوض', '0791111009', 'الدوار السابع', ['vip']],
            ['خالد المومني', '0791111010', 'مرج الحمام', []],
            ['رزان الحسن', '0791111011', 'الرابية', ['جديد']],
            ['أحمد كمال', '0791111012', 'الصويفية', ['شركات']],
            ['سلمى فريجات', '0791111013', 'عبدون', []],
            ['تالا الزعبي', '0791111014', 'خلدا', ['جديد']],
        ])->map(fn ($data) => Customer::updateOrCreate(
            ['store_id' => $store->id, 'phone' => $data[1]],
            [
                'name' => $data[0],
                'address' => 'عمّان، '.$data[2],
                'tags' => $data[3],
            ]
        ));

        if (Order::where('store_id', $store->id)->exists()) {
            return;
        }

        $productList = $products->values();
        $customerList = $customers->values();

        $plan = [
            // status, day offset for scheduled date, time
            ['delivered', -48, '12:30'], ['delivered', -45, '17:00'], ['delivered', -42, '11:00'],
            ['delivered', -39, '19:00'], ['delivered', -36, '14:00'], ['delivered', -33, '16:30'],
            ['delivered', -30, '12:00'], ['delivered', -27, '18:00'], ['delivered', -24, '10:30'],
            ['delivered', -21, '15:00'], ['delivered', -18, '13:00'], ['delivered', -16, '17:30'],
            ['delivered', -14, '11:30'], ['delivered', -12, '19:30'], ['delivered', -9, '12:00'],
            ['delivered', -7, '16:00'], ['delivered', -5, '14:30'], ['delivered', -3, '18:00'],
            ['cancelled', -20, '15:00'], ['cancelled', -8, '13:30'],
            ['out_for_delivery', 0, '12:00'], ['out_for_delivery', 0, '14:00'],
            ['ready', 0, '16:00'], ['ready', 0, '18:30'],
            ['in_production', 1, '12:00'], ['in_production', 2, '16:00'],
            ['confirmed', 1, '17:00'], ['confirmed', 2, '11:00'], ['confirmed', 5, '15:30'],
            ['new', 3, '18:00'], ['new', 4, '13:00'], ['new', 6, '16:00'],
        ];

        $paymentCycle = ['cash', 'cliq', 'cash', 'bank_transfer', 'cliq', 'cash'];
        $dayCounters = [];

        foreach ($plan as $index => [$statusValue, $dayOffset, $time]) {
            $status = OrderStatus::from($statusValue);
            $scheduled = now()->startOfDay()->addDays($dayOffset)->setTimeFromTimeString($time);

            $created = $dayOffset < 0
                ? $scheduled->copy()->subDays(2)->setTime(10 + ($index % 7), ($index * 7) % 60)
                : now()->copy()->subDays(1)->setTime(11 + ($index % 5), ($index * 11) % 60);

            $payment = $paymentCycle[$index % count($paymentCycle)];
            $fulfillment = $index % 4 === 3 ? FulfillmentType::Pickup : FulfillmentType::Delivery;
            $customer = $customerList[$index % 12 < 10 ? $index % 12 : $index % 14];

            $lineProducts = [$productList[$index % $productList->count()]];
            if ($index % 3 === 0) {
                $lineProducts[] = $productList[($index + 3) % $productList->count()];
            }

            $items = [];
            $subtotal = 0;

            foreach ($lineProducts as $pIndex => $product) {
                $quantity = ($index + $pIndex) % 4 === 0 ? 2 : 1;
                $unit = $product->price_fils;
                $optionDetails = [];

                if ($product->options->isNotEmpty()) {
                    foreach ($product->options as $option) {
                        if (! $option->is_required && ($index + $option->id) % 3 === 0) {
                            continue;
                        }

                        $value = $option->values[($index + $option->id) % $option->values->count()];
                        $unit += $value->price_delta_fils;
                        $optionDetails[] = [
                            'name' => $option->name,
                            'value' => $value->value,
                            'delta' => $value->price_delta_fils,
                        ];
                    }
                }

                $lineTotal = $unit * $quantity;
                $subtotal += $lineTotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'options' => $optionDetails,
                    'line_total' => $lineTotal,
                ];
            }

            $deliveryFee = $fulfillment === FulfillmentType::Delivery ? $store->delivery_fee_fils : 0;
            $total = $subtotal + $deliveryFee;
            $deposit = $payment !== 'cash' ? (int) round($total * $store->deposit_percent / 100) : 0;

            $depositStatus = match (true) {
                $deposit === 0 => DepositStatus::None,
                $status === OrderStatus::Delivered => DepositStatus::Paid,
                $status === OrderStatus::Cancelled => DepositStatus::Refunded,
                default => DepositStatus::Pending,
            };

            $dateKey = $created->format('ymd');
            $dayCounters[$dateKey] = ($dayCounters[$dateKey] ?? 0) + 1;

            $order = Order::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'number' => 'AB-'.$dateKey.'-'.str_pad((string) $dayCounters[$dateKey], 3, '0', STR_PAD_LEFT),
                'public_token' => (string) Str::uuid(),
                'status' => $status,
                'fulfillment_type' => $fulfillment,
                'scheduled_at' => $scheduled,
                'delivery_address' => $fulfillment === FulfillmentType::Delivery ? $customer->address : null,
                'subtotal_fils' => $subtotal,
                'delivery_fee_fils' => $deliveryFee,
                'discount_fils' => 0,
                'total_fils' => $total,
                'deposit_fils' => $deposit,
                'deposit_status' => $depositStatus,
                'payment_method' => PaymentMethod::from($payment),
                'balance_due_fils' => max(0, $total - ($depositStatus === DepositStatus::Paid ? $deposit : 0)),
                'customer_notes' => $index % 5 === 0 ? 'يا ريت الكتابة تكون بخط صغير وهادئ.' : null,
                'internal_notes' => $index % 7 === 0 ? 'العميل حساس من المكسرات — تم التأكيد.' : null,
                'source' => 'storefront',
                'consent_at' => $created,
            ]);

            $order->forceFill(['created_at' => $created, 'updated_at' => $created])->save();

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'unit_price_fils' => $item['unit'],
                    'options' => $item['options'],
                    'line_total_fils' => $item['line_total'],
                ]);
            }

            $this->seedEvents($order, $created, $scheduled, $status);
        }

        $this->seedNotifications($store, $owner);
        $this->seedMessages($store);
    }

    protected function seedEvents(Order $order, Carbon $created, Carbon $scheduled, OrderStatus $status): void
    {
        $createdEvent = $order->events()->make(['type' => 'created', 'payload' => ['total' => $order->total_fils]]);
        $createdEvent->created_at = $created;
        $createdEvent->updated_at = $created;
        $createdEvent->save();

        $path = match ($status) {
            OrderStatus::New => [],
            OrderStatus::Confirmed => [OrderStatus::Confirmed],
            OrderStatus::InProduction => [OrderStatus::Confirmed, OrderStatus::InProduction],
            OrderStatus::Ready => [OrderStatus::Confirmed, OrderStatus::InProduction, OrderStatus::Ready],
            OrderStatus::OutForDelivery => [OrderStatus::Confirmed, OrderStatus::InProduction, OrderStatus::Ready, OrderStatus::OutForDelivery],
            OrderStatus::Delivered => [OrderStatus::Confirmed, OrderStatus::InProduction, OrderStatus::Ready, OrderStatus::Delivered],
            OrderStatus::Cancelled => [OrderStatus::Cancelled],
        };

        $previous = OrderStatus::New;
        $time = $created->copy();

        foreach ($path as $position => $next) {
            $time = $time->copy()->addHours(5);
            if ($time->isFuture()) {
                $time = now()->subMinutes(90 - ($position * 20));
            }
            if ($time->greaterThan($scheduled->copy()->addHours(2))) {
                $time = $scheduled->copy()->addHours(1);
            }

            $event = $order->events()->make([
                'type' => 'status_changed',
                'payload' => ['from' => $previous->label(), 'to' => $next->label(), 'from_value' => $previous->value, 'to_value' => $next->value],
            ]);
            $event->created_at = $time;
            $event->updated_at = $time;
            $event->save();

            $previous = $next;
        }

        if ($order->deposit_status === DepositStatus::Paid) {
            $depositEvent = $order->events()->make(['type' => 'deposit_paid', 'payload' => ['amount' => $order->deposit_fils]]);
            $depositEvent->created_at = $created->copy()->addHours(2);
            $depositEvent->updated_at = $created->copy()->addHours(2);
            $depositEvent->save();
        }
    }

    protected function seedNotifications(Store $store, User $owner): void
    {
        $orders = Order::forStore($store)->latest('scheduled_at')->take(8)->get();

        foreach ($orders->take(6) as $i => $order) {
            $notification = StoreNotification::create([
                'store_id' => $store->id,
                'user_id' => $owner->id,
                'type' => $i % 3 === 0 ? 'order_created' : ($i % 3 === 1 ? 'deposit_paid' : 'order_confirmed'),
                'title' => $i % 3 === 0 ? 'طلب جديد '.$order->number : 'تحديث على الطلب '.$order->number,
                'body' => $order->customer->name.' • '.$order->totalFormatted(),
                'data' => ['order_id' => $order->id],
                'read_at' => $i > 2 ? now()->subHours($i) : null,
            ]);
            $notification->forceFill(['created_at' => now()->subMinutes($i * 37 + 8)])->save();
        }
    }

    protected function seedMessages(Store $store): void
    {
        $orders = Order::forStore($store)->whereIn('status', [OrderStatus::Delivered, OrderStatus::Ready])->take(5)->get();

        foreach ($orders as $i => $order) {
            MessageLog::create([
                'store_id' => $store->id,
                'order_id' => $order->id,
                'channel' => 'whatsapp',
                'template' => $i % 2 === 0 ? 'order_confirmation' : 'order_ready',
                'to' => $order->customer->phone,
                'status' => 'opened',
                'body' => 'رسالة تجريبية — لم يتم إرسالها فعلياً.',
                'sent_at' => now()->subHours($i * 5 + 2),
            ]);
        }
    }
}
