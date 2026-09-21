<?php

namespace App\Services\AiAssistant;

use App\Models\Product;
use App\Support\DecimalMath;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AssistantEngine
{
    public function __construct(
        protected AssistantTools $tools,
        protected OpenAiBridge $openAi,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function handle(string $message, array $payload = []): array
    {
        $message = trim($message);
        $action = $payload['action'] ?? null;
        $context = $this->context();

        if ($action === 'select_product') {
            return $this->onProductSelected((int) ($payload['product_id'] ?? 0), $payload['intent'] ?? ($context['pending_intent'] ?? 'stock'));
        }

        if ($action === 'calc_qty') {
            return $this->onCalcQty(
                (int) ($payload['product_id'] ?? ($context['product_id'] ?? 0)),
                (float) ($payload['qty'] ?? 1),
                (string) ($payload['unit'] ?? 'piece')
            );
        }

        if ($action === 'quick_reply') {
            $message = (string) ($payload['text'] ?? $message);
        }

        if ($message === '' || in_array($message, ['مرحبا', 'السلام عليكم', 'هاي', 'hi', 'hello', '/start'], true)) {
            return $this->welcome();
        }

        // انتظار كمية بعد اختيار منتج
        if (! empty($context['awaiting']) && $context['awaiting'] === 'qty' && ! empty($context['product_id'])) {
            $parsed = $this->parseQtyAndUnit($message);
            if ($parsed) {
                return $this->onCalcQty((int) $context['product_id'], $parsed['qty'], $parsed['unit']);
            }
        }

        // أولاً: أدوات النظام المحلية (بيانات حقيقية)
        $intent = $this->detectIntent($message);
        if ($intent['name'] !== 'fallback') {
            return match ($intent['name']) {
                'inventory_summary' => $this->replyInventorySummary(),
                'low_stock' => $this->replyLowStock(),
                'out_of_stock' => $this->replyOutOfStock(),
                'sales_summary' => $this->replySalesSummary(),
                'clients_summary' => $this->replyClientsSummary($intent['query'] ?? null),
                'suppliers_summary' => $this->replySuppliersSummary(),
                'expenses_summary' => $this->replyExpensesSummary(),
                'cash_balance' => $this->replyCash(),
                'categories' => $this->replyCategories(),
                'reports' => $this->replyReports($intent['query'] ?? $message),
                'recent_orders' => $this->replyRecentOrders(),
                'create_order' => $this->replyCreateOrder(),
                'product_exists' => $this->replyProductExists($intent['query'] ?? ''),
                'product_query' => $this->replyProductQuery($intent['query'] ?? $message, $intent['focus'] ?? 'stock'),
                'help' => $this->welcome(),
                default => $this->replyFallback($message),
            };
        }

        // ثانياً: نموذج لغوي اختياري إن وُجد مفتاح OpenAI
        $llm = $this->openAi->reply($message);
        if ($llm !== null) {
            return $llm;
        }

        return $this->replyFallback($message);
    }

    /**
     * @return array<string, mixed>
     */
    protected function welcome(): array
    {
        $this->forgetAwaiting();

        return [
            'reply' => $this->openAi->enabled()
                ? "مرحباً 👋 أنا مساعدك الذكي (OpenAI + بيانات النظام).\nأسألني بالعربية عن المخزون، قيمة الكراتين، المبيعات، العملاء، التقارير، أو إنشاء طلب."
                : "مرحباً 👋 أنا مساعدك الذكي للنظام.\nأسألني بالعربية عن المخزون، قيمة الكراتين، المبيعات، العملاء، التقارير، أو إنشاء طلب.",
            'suggestions' => [
                ['label' => '📦 مخزون منتج', 'text' => 'كم مخزون'],
                ['label' => '🧮 قيمة 10 كراتين', 'text' => 'احسب قيمة 10 كراتين'],
                ['label' => '📊 ملخص المخزون', 'text' => 'ملخص المخزون'],
                ['label' => '💰 مبيعات اليوم', 'text' => 'مبيعات اليوم'],
                ['label' => '⚠️ نواقص', 'text' => 'المنتجات الناقصة'],
                ['label' => '🧾 تقارير', 'text' => 'عرض التقارير'],
                ['label' => '🛒 طلب سريع', 'text' => 'إنشاء طلب'],
            ],
            'products' => [],
            'links' => [],
            'meta' => [],
        ];
    }

    /**
     * @return array{name: string, query?: string, focus?: string}
     */
    protected function detectIntent(string $message): array
    {
        $m = mb_strtolower($message);

        if ($this->hasAny($m, ['مساعدة', 'ماذا تستطيع', 'help', 'أوامر'])) {
            return ['name' => 'help'];
        }

        if ($this->hasAny($m, ['إنشاء طلب', 'طلب جديد', 'بيع مباشر', 'فاتورة بيع', 'طلب سريع'])) {
            return ['name' => 'create_order'];
        }

        if ($this->hasAny($m, ['تقارير', 'تقرير', 'اطلع لي تقرير', 'وين تقرير'])) {
            return ['name' => 'reports', 'query' => $message];
        }

        if ($this->hasAny($m, ['مبيعات اليوم', 'ملخص مبيعات', 'كم بعنا', 'إجمالي المبيعات', 'مبيعات'])) {
            return ['name' => 'sales_summary'];
        }

        if ($this->hasAny($m, ['ملخص المخزون', 'قيمة المخزون', 'جرد', 'إجمالي المخزون'])) {
            return ['name' => 'inventory_summary'];
        }

        if ($this->hasAny($m, ['ناقص', 'نواقص', 'منخفض', 'قليل المخزون', 'low stock'])) {
            return ['name' => 'low_stock'];
        }

        if ($this->hasAny($m, ['نفد', 'صفر', 'غير متوفر', 'خلصت', 'out of stock'])) {
            return ['name' => 'out_of_stock'];
        }

        if ($this->hasAny($m, ['عملاء', 'مديونية عميل', 'دين عميل', 'رصيد عميل'])) {
            $q = $this->extractAfterKeywords($message, ['عميل', 'عملاء']);

            return ['name' => 'clients_summary', 'query' => $q];
        }

        if ($this->hasAny($m, ['مورد', 'موردين', 'مشتريات'])) {
            return ['name' => 'suppliers_summary'];
        }

        if ($this->hasAny($m, ['مصروف', 'مصروفات'])) {
            return ['name' => 'expenses_summary'];
        }

        if ($this->hasAny($m, ['خزنة', 'كاش', 'نقدية', 'رصيد الصندوق'])) {
            return ['name' => 'cash_balance'];
        }

        if ($this->hasAny($m, ['أقسام', 'تصنيفات', 'فئات'])) {
            return ['name' => 'categories'];
        }

        if ($this->hasAny($m, ['آخر طلبات', 'طلبات أخيرة', 'أحدث الطلبات'])) {
            return ['name' => 'recent_orders'];
        }

        if ($this->hasAny($m, ['موجود', 'هل يوجد', 'في عندي', 'عندي منتج'])) {
            $q = $this->extractProductQuery($message);

            return ['name' => 'product_exists', 'query' => $q ?: $message];
        }

        if ($this->hasAny($m, ['مخزون', 'كمية', 'كم باقي', 'كام في المخزن', 'كرتونة', 'كراتين', 'حبة', 'كيلو', 'قيمة', 'سعر', 'احسب'])) {
            $focus = 'stock';
            if ($this->hasAny($m, ['قيمة', 'سعر', 'احسب', 'تكلفة'])) {
                $focus = 'value';
            }
            if ($this->hasAny($m, ['كرتونة', 'كراتين', 'نصف'])) {
                $focus = 'carton';
            }

            return [
                'name' => 'product_query',
                'query' => $this->extractProductQuery($message),
                'focus' => $focus,
            ];
        }

        // لو الرسالة اسم منتج محتمل
        $products = $this->tools->searchProducts($message, 5);
        if ($products->isNotEmpty()) {
            return ['name' => 'product_query', 'query' => $message, 'focus' => 'stock'];
        }

        return ['name' => 'fallback'];
    }

    protected function replyInventorySummary(): array
    {
        $s = $this->tools->inventorySummary();

        return [
            'reply' => "📦 ملخص المخزون (بيانات حقيقية):\n"
                ."• عدد الأصناف: {$s['totalSku']}\n"
                .'• إجمالي الوحدات: '.DecimalMath::display($s['totalStock'])."\n"
                .'• قيمة الشراء: '.DecimalMath::display($s['purchaseValue'])." ج.س\n"
                .'• قيمة البيع: '.DecimalMath::display($s['saleValue'])." ج.س\n"
                ."• منخفضة المخزون: {$s['lowStock']}\n"
                ."• نفدت: {$s['outOfStock']}",
            'suggestions' => [
                ['label' => 'نواقص', 'text' => 'المنتجات الناقصة'],
                ['label' => 'تقرير الجرد', 'text' => 'تقرير المخزون'],
            ],
            'products' => [],
            'links' => [[
                'label' => 'فتح تقرير الجرد',
                'url' => route('dashboard.reports.inventory.report'),
            ]],
            'meta' => $s,
        ];
    }

    protected function replyLowStock(): array
    {
        $products = $this->tools->lowStockProducts();

        return [
            'reply' => $products->isEmpty()
                ? 'لا توجد منتجات منخفضة المخزون حالياً ✅'
                : 'هذه المنتجات منخفضة المخزون (≤ 10). اختر منتجاً لعرض التفاصيل:',
            'suggestions' => [
                ['label' => 'نفدت', 'text' => 'المنتجات النافدة'],
                ['label' => 'ملخص المخزون', 'text' => 'ملخص المخزون'],
            ],
            'products' => $products->values()->all(),
            'product_actions' => [
                ['key' => 'stock', 'label' => 'المخزون'],
                ['key' => 'value', 'label' => 'القيمة'],
                ['key' => 'carton', 'label' => 'كراتين'],
            ],
            'links' => [],
            'meta' => [],
        ];
    }

    protected function replyOutOfStock(): array
    {
        $products = $this->tools->outOfStockProducts();

        return [
            'reply' => $products->isEmpty()
                ? 'لا توجد منتجات نافدة ✅'
                : 'منتجات نفد مخزونها:',
            'suggestions' => [],
            'products' => $products->values()->all(),
            'product_actions' => [
                ['key' => 'stock', 'label' => 'التفاصيل'],
            ],
            'links' => [],
            'meta' => [],
        ];
    }

    protected function replySalesSummary(): array
    {
        $s = $this->tools->salesSummary();

        return [
            'reply' => "💰 مبيعات {$s['period']}:\n"
                ."• عدد الطلبات: {$s['orders_count']}\n"
                .'• الإجمالي: '.DecimalMath::display($s['sales_total'])." ج.س\n"
                .'• المدفوع: '.DecimalMath::display($s['paid'])." ج.س\n"
                .'• المتبقي: '.DecimalMath::display($s['remaining'])." ج.س\n"
                .'• الربح: '.DecimalMath::display($s['profit']).' ج.س',
            'suggestions' => [
                ['label' => 'آخر الطلبات', 'text' => 'آخر طلبات'],
                ['label' => 'إنشاء طلب', 'text' => 'إنشاء طلب'],
            ],
            'products' => [],
            'links' => [[
                'label' => 'تقرير المبيعات',
                'url' => route('dashboard.reports.summary'),
            ]],
            'meta' => $s,
        ];
    }

    protected function replyClientsSummary(?string $query): array
    {
        if ($query) {
            $clients = $this->tools->searchClients($query);
            if ($clients->isEmpty()) {
                return $this->textReply("لم أجد عميلاً باسم «{$query}».");
            }

            $lines = $clients->map(fn ($c) => '• '.$c['name'].' — متبقي: '.DecimalMath::display($c['remaining']).' ج.س ('.$c['orders_count'].' طلب)')->implode("\n");

            return $this->textReply("نتائج العملاء:\n".$lines, [
                ['label' => 'تقرير العملاء', 'text' => 'تقرير العملاء'],
            ], [[
                'label' => 'فتح تقرير العملاء',
                'url' => route('dashboard.reports.reports.index'),
            ]]);
        }

        $s = $this->tools->clientsSummary();

        return $this->textReply(
            "👥 العملاء:\n• العدد: {$s['clients_count']}\n• عليهم مديونية: {$s['with_debt']}\n• إجمالي المديونية: ".DecimalMath::display($s['total_debt']).' ج.س',
            [['label' => 'تقرير العملاء', 'text' => 'تقرير العملاء']],
            [['label' => 'فتح تقرير العملاء', 'url' => route('dashboard.reports.reports.index')]]
        );
    }

    protected function replySuppliersSummary(): array
    {
        $s = $this->tools->suppliersSummary();

        return $this->textReply(
            "🚚 الموردين:\n• العدد: {$s['suppliers_count']}\n• فواتير الشراء: {$s['invoices_count']}\n• غير مسددة: {$s['unpaid_invoices']}",
            [],
            [['label' => 'تقرير الموردين', 'url' => route('dashboard.reports.suppliers.index')]]
        );
    }

    protected function replyExpensesSummary(): array
    {
        $s = $this->tools->expensesSummary();

        return $this->textReply(
            "💸 المصروفات ({$s['period']}):\n• العدد: {$s['count']}\n• الإجمالي: ".DecimalMath::display($s['total']).' ج.س',
            [],
            [['label' => 'تقرير المصروفات', 'url' => route('dashboard.reports.reports.expenses')]]
        );
    }

    protected function replyCash(): array
    {
        $s = $this->tools->cashBalance();

        return $this->textReply(
            "🏦 الخزنة:\n• الرصيد: ".DecimalMath::display($s['balance'])." ج.س\n• دخول اليوم: ".DecimalMath::display($s['today_in'])." ج.س\n• خروج اليوم: ".DecimalMath::display($s['today_out']).' ج.س',
            [],
            [['label' => 'تقرير الخزنة', 'url' => route('dashboard.reports.report.cash')]]
        );
    }

    protected function replyCategories(): array
    {
        $cats = $this->tools->categoriesSummary();
        $lines = $cats->take(15)->map(fn ($c) => '• '.$c['name'].' — '.$c['products_count'].' منتج — قيمة '.DecimalMath::display($c['stock_value']).' ج.س')->implode("\n");

        return $this->textReply("📂 الأقسام:\n".$lines);
    }

    protected function replyReports(string $query): array
    {
        $clean = trim(preg_replace('/تقارير?|عرض|اطلع|وين|أين|لي/u', '', $query) ?? '');
        $reports = $clean !== '' ? $this->tools->findReport($clean) : $this->tools->reportsCatalog();

        if ($reports === []) {
            $reports = $this->tools->reportsCatalog();
        }

        $lines = collect($reports)->take(8)->map(fn ($r, $i) => ($i + 1).') '.$r['title'].' — '.$r['description'])->implode("\n");

        return [
            'reply' => "📑 التقارير المتاحة:\n".$lines."\n\nاضغط رابطاً لفتح التقرير مباشرة.",
            'suggestions' => [
                ['label' => 'المخزون', 'text' => 'تقرير المخزون'],
                ['label' => 'المبيعات', 'text' => 'تقرير المبيعات'],
                ['label' => 'الأرباح', 'text' => 'تقرير الأرباح'],
            ],
            'products' => [],
            'links' => collect($reports)->take(8)->map(fn ($r) => [
                'label' => $r['title'],
                'url' => $r['url'],
            ])->values()->all(),
            'meta' => [],
        ];
    }

    protected function replyRecentOrders(): array
    {
        $orders = $this->tools->recentOrders();
        if ($orders->isEmpty()) {
            return $this->textReply('لا توجد طلبات بعد.');
        }

        $lines = $orders->map(fn ($o) => '• #'.$o['order_number'].' — '.$o['client'].' — '.DecimalMath::display($o['total']).' ج.س — '.$o['date'])->implode("\n");

        return [
            'reply' => "🧾 آخر الطلبات:\n".$lines,
            'suggestions' => [['label' => 'إنشاء طلب', 'text' => 'إنشاء طلب']],
            'products' => [],
            'links' => $orders->take(5)->map(fn ($o) => [
                'label' => 'طلب #'.$o['order_number'],
                'url' => $o['url'],
            ])->values()->all(),
            'meta' => [],
        ];
    }

    protected function replyCreateOrder(): array
    {
        return [
            'reply' => 'يمكنك إنشاء طلب بسرعة من شاشة البيع المباشر. بعد فتحها اختر المنتجات والكميات (حبة / نصف كرتونة / كرتونة).',
            'suggestions' => [
                ['label' => 'مخزون منتج', 'text' => 'كم مخزون'],
                ['label' => 'مبيعات اليوم', 'text' => 'مبيعات اليوم'],
            ],
            'products' => [],
            'links' => [
                ['label' => '🛒 فتح البيع المباشر', 'url' => route('dashboard.direct-sale')],
                ['label' => 'طلبات العملاء', 'url' => route('dashboard.orders.index')],
            ],
            'meta' => [],
        ];
    }

    protected function replyProductExists(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return $this->askPickProduct('product_exists', 'اكتب اسم المنتج لأتحقق إن كان موجوداً:');
        }

        $result = $this->tools->productExists($query);
        if (! $result['exists']) {
            return $this->textReply("❌ لا يوجد منتج باسم قريب من «{$query}».", [
                ['label' => 'إضافة منتج', 'text' => 'إنشاء طلب'],
            ], [['label' => 'إضافة منتج جديد', 'url' => route('dashboard.products.create')]]);
        }

        return [
            'reply' => "✅ نعم، وجدت {$result['count']} منتجاً مطابقاً. اختر واحداً:",
            'suggestions' => [],
            'products' => $result['products'],
            'product_actions' => [
                ['key' => 'stock', 'label' => 'المخزون'],
                ['key' => 'value', 'label' => 'القيمة'],
                ['key' => 'carton', 'label' => 'كراتين'],
            ],
            'links' => [],
            'meta' => [],
        ];
    }

    protected function replyProductQuery(?string $query, string $focus = 'stock'): array
    {
        $query = trim((string) $query);

        // استخراج كمية من الرسالة مثل: قيمة 10 كراتين شاي
        $parsedQty = $this->parseQtyAndUnit($query !== '' ? $query : '');

        if ($query === '' || $this->isGenericStockPhrase($query)) {
            $this->putContext(['pending_intent' => $focus]);

            return $this->askPickProduct($focus, 'اختر منتجاً من القائمة، أو اكتب اسمه:');
        }

        $products = $this->tools->searchProducts($this->cleanProductName($query), 10);

        if ($products->isEmpty()) {
            return $this->textReply("لم أجد منتجاً يطابق «{$query}». جرّب اسماً أقصر أو اختر من القائمة.", [
                ['label' => 'عرض منتجات', 'text' => 'كم مخزون'],
            ]);
        }

        if ($products->count() === 1) {
            $product = $products->first();
            if ($parsedQty && in_array($focus, ['value', 'carton'], true)) {
                return $this->onCalcQty((int) $product['id'], $parsedQty['qty'], $parsedQty['unit']);
            }

            return $this->onProductSelected((int) $product['id'], $focus);
        }

        $this->putContext(['pending_intent' => $focus]);

        return [
            'reply' => 'وجدت عدة منتجات. اختر ماذا تريد معرفته:',
            'suggestions' => [],
            'products' => $products->values()->all(),
            'product_actions' => [
                ['key' => 'stock', 'label' => 'المخزون'],
                ['key' => 'value', 'label' => 'القيمة'],
                ['key' => 'carton', 'label' => 'كراتين'],
            ],
            'links' => [],
            'meta' => ['focus' => $focus],
        ];
    }

    protected function onProductSelected(int $productId, string $intent = 'stock'): array
    {
        $card = $this->tools->findProduct($productId);
        if (! $card) {
            return $this->textReply('المنتج غير موجود.');
        }

        $this->putContext([
            'product_id' => $productId,
            'pending_intent' => $intent,
            'awaiting' => in_array($intent, ['value', 'carton'], true) ? 'qty' : null,
        ]);

        if ($intent === 'carton' || $intent === 'value') {
            $bulk = $card['pieces_per_carton'];

            return [
                'reply' => "اخترت: {$card['name']}\n"
                    ."المخزون الحالي: {$card['stock_display']}\n"
                    ."الحبة في الكرتونة: {$bulk}\n\n"
                    .'كم الكمية التي تريد حسابها؟ مثال: `10 كراتين` أو `5 حبة` أو `2 نصف`.',
                'suggestions' => [
                    ['label' => '10 كراتين', 'text' => '10 كراتين'],
                    ['label' => '5 كراتين', 'text' => '5 كراتين'],
                    ['label' => '1 نصف', 'text' => '1 نصف'],
                    ['label' => '20 حبة', 'text' => '20 حبة'],
                    ['label' => 'عرض المخزون فقط', 'text' => 'مخزون'],
                ],
                'products' => [$card],
                'product_actions' => [],
                'calc_form' => [
                    'product_id' => $productId,
                    'units' => $this->unitsForCard($card),
                ],
                'links' => [[
                    'label' => 'تعديل المنتج',
                    'url' => route('dashboard.products.edit', $productId),
                ]],
                'meta' => $card,
            ];
        }

        return $this->formatProductStockReply($card);
    }

    protected function onCalcQty(int $productId, float $qty, string $unit): array
    {
        $product = Product::find($productId);
        if (! $product) {
            return $this->textReply('المنتج غير موجود.');
        }

        $calc = $this->tools->calculateQuantityValue($product, $qty, $unit);
        $card = $this->tools->productCard($product);
        $this->putContext(['product_id' => $productId, 'awaiting' => null]);

        $avail = $calc['available'] ? '✅ متوفر في المخزون' : '⚠️ غير كافٍ — النقص '.DecimalMath::display($calc['shortage']);

        return [
            'reply' => "🧮 حساب «{$calc['product']}»\n"
                .'• الكمية: '.DecimalMath::display($calc['qty']).' '.$calc['unit_label']."\n"
                .'• ما يعادل: '.DecimalMath::display($calc['pieces_equivalent'])." وحدة مخزون\n"
                .'• سعر الوحدة (بيع): '.DecimalMath::display($calc['sale_unit_price'])." ج.س\n"
                .'• الإجمالي (بيع): '.DecimalMath::display($calc['sale_total'])." ج.س\n"
                .'• الإجمالي (شراء/تكلفة): '.DecimalMath::display($calc['purchase_total'])." ج.س\n"
                ."• المخزون الحالي: {$calc['stock_display']}\n"
                ."• الحالة: {$avail}",
            'suggestions' => [
                ['label' => 'حساب آخر', 'text' => 'احسب قيمة'],
                ['label' => 'إنشاء طلب', 'text' => 'إنشاء طلب'],
                ['label' => 'المخزون', 'text' => 'كم مخزون '.$product->name],
            ],
            'products' => [$card],
            'calc_form' => [
                'product_id' => $productId,
                'units' => $this->unitsForCard($card),
            ],
            'links' => [
                ['label' => 'بيع مباشر', 'url' => route('dashboard.direct-sale')],
                ['label' => 'تعديل المنتج', 'url' => route('dashboard.products.edit', $productId)],
            ],
            'meta' => $calc,
        ];
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    protected function formatProductStockReply(array $card): array
    {
        $cartonLine = $card['carton_stock'] !== null
            ? '• بالكراتين تقريباً: '.DecimalMath::display($card['carton_stock'])." كرتونة\n"
            : '';

        return [
            'reply' => "📦 {$card['name']}\n"
                ."• القسم: {$card['category']}\n"
                ."• وحدة القياس: {$card['measure_label']}\n"
                ."• المخزون: {$card['stock_display']}\n"
                .$cartonLine
                .'• سعر البيع: '.DecimalMath::display($card['sale_price'])." / وحدة\n"
                .'• سعر الشراء: '.DecimalMath::display($card['purchase_price'])." / وحدة\n"
                .'• قيمة المخزون (بيع): '.DecimalMath::display($card['stock_value_sale'])." ج.س\n"
                .'• قيمة المخزون (شراء): '.DecimalMath::display($card['stock_value_purchase']).' ج.س',
            'suggestions' => [
                ['label' => 'احسب كراتين', 'text' => 'احسب قيمة كراتين'],
                ['label' => 'إنشاء طلب', 'text' => 'إنشاء طلب'],
            ],
            'products' => [$card],
            'product_actions' => [
                ['key' => 'carton', 'label' => 'احسب كراتين'],
                ['key' => 'value', 'label' => 'احسب قيمة'],
            ],
            'calc_form' => [
                'product_id' => $card['id'],
                'units' => $this->unitsForCard($card),
            ],
            'links' => [
                ['label' => 'عرض المنتج', 'url' => route('dashboard.products.show', $card['id'])],
                ['label' => 'بيع مباشر', 'url' => route('dashboard.direct-sale')],
            ],
            'meta' => $card,
        ];
    }

    protected function askPickProduct(string $intent, string $prompt): array
    {
        $products = $this->tools->searchProducts(null, 12);
        $this->putContext(['pending_intent' => $intent]);

        return [
            'reply' => $prompt,
            'suggestions' => [
                ['label' => 'نواقص', 'text' => 'المنتجات الناقصة'],
                ['label' => 'ملخص المخزون', 'text' => 'ملخص المخزون'],
            ],
            'products' => $products->values()->all(),
            'product_actions' => [
                ['key' => 'stock', 'label' => 'المخزون'],
                ['key' => 'value', 'label' => 'القيمة'],
                ['key' => 'carton', 'label' => 'كراتين'],
            ],
            'links' => [],
            'meta' => ['intent' => $intent],
        ];
    }

    protected function replyFallback(string $message): array
    {
        $reports = $this->tools->findReport($message);
        if (count($reports) === 1) {
            return $this->replyReports($message);
        }

        $products = $this->tools->searchProducts($message, 6);
        if ($products->isNotEmpty()) {
            return [
                'reply' => 'هل تقصد أحد هذه المنتجات؟',
                'suggestions' => $this->welcome()['suggestions'],
                'products' => $products->values()->all(),
                'product_actions' => [
                    ['key' => 'stock', 'label' => 'المخزون'],
                    ['key' => 'value', 'label' => 'القيمة'],
                    ['key' => 'carton', 'label' => 'كراتين'],
                ],
                'links' => [],
                'meta' => [],
            ];
        }

        return [
            'reply' => "لم أفهم «{$message}» بالكامل.\nيمكنني: المخزون، حساب كراتين، المبيعات، العملاء، المصروفات، الخزنة، التقارير، وإنشاء طلب.",
            'suggestions' => $this->welcome()['suggestions'],
            'products' => [],
            'links' => [],
            'meta' => [],
        ];
    }

    /**
     * @param  array<int, array{label: string, text: string}>  $suggestions
     * @param  array<int, array{label: string, url: string}>  $links
     * @return array<string, mixed>
     */
    protected function textReply(string $reply, array $suggestions = [], array $links = []): array
    {
        return [
            'reply' => $reply,
            'suggestions' => $suggestions,
            'products' => [],
            'links' => $links,
            'meta' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<int, array{value: string, label: string}>
     */
    protected function unitsForCard(array $card): array
    {
        if (($card['measure_unit'] ?? '') === 'kilo') {
            return [['value' => 'kilo', 'label' => 'كيلو']];
        }

        return [
            ['value' => 'piece', 'label' => 'حبة'],
            ['value' => 'half_carton', 'label' => 'نصف كرتونة'],
            ['value' => 'carton', 'label' => 'كرتونة كاملة'],
        ];
    }

    /**
     * @return array{qty: float, unit: string}|null
     */
    protected function parseQtyAndUnit(string $message): ?array
    {
        $m = mb_strtolower(trim($message));
        if ($m === '' || $m === 'مخزون') {
            return null;
        }

        if (! preg_match('/(\d+(?:[.,]\d+)?)/u', $m, $match)) {
            return null;
        }

        $qty = (float) str_replace(',', '.', $match[1]);
        $unit = 'piece';

        if (preg_match('/نصف/u', $m)) {
            $unit = 'half_carton';
        } elseif (preg_match('/كراتين|كرتونة|كرتون|عبوة/u', $m)) {
            $unit = 'carton';
        } elseif (preg_match('/كيلو/u', $m)) {
            $unit = 'kilo';
        } elseif (preg_match('/حبة|قطعة|قطع/u', $m)) {
            $unit = 'piece';
        } elseif (preg_match('/كراتين|كرتونة/u', $message)) {
            $unit = 'carton';
        }

        // إذا قال "احسب قيمة 10" بدون وحدة والكاريكة carton intent
        $context = $this->context();
        if ($unit === 'piece' && ($context['pending_intent'] ?? '') === 'carton') {
            $unit = 'carton';
        }

        return ['qty' => $qty, 'unit' => $unit];
    }

    protected function extractProductQuery(string $message): string
    {
        $clean = preg_replace('/كم|المخزون|مخزون|كمية|باقي|في المخزن|قيمة|سعر|احسب|كراتين|كرتونة|حبة|قطعة|نصف|من|عن|هل|يوجد|موجود|عندي|بضاعة/u', ' ', $message) ?? $message;
        $clean = preg_replace('/\d+(?:[.,]\d+)?/u', ' ', $clean) ?? $clean;
        $clean = trim(preg_replace('/\s+/u', ' ', $clean) ?? '');

        return $clean;
    }

    protected function cleanProductName(string $query): string
    {
        return $this->extractProductQuery($query) ?: $query;
    }

    protected function isGenericStockPhrase(string $query): bool
    {
        $q = mb_strtolower(trim($query));

        return in_array($q, [
            '', 'مخزون', 'كم مخزون', 'كمية', 'كراتين', 'احسب قيمة', 'احسب قيمة كراتين', 'قيمة',
        ], true) || $this->extractProductQuery($query) === '';
    }

    protected function extractAfterKeywords(string $message, array $keywords): ?string
    {
        foreach ($keywords as $kw) {
            if (preg_match('/'.$kw.'\s+(.+)$/u', $message, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $needles
     */
    protected function hasAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (Str::contains($haystack, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function context(): array
    {
        return Session::get('ai_assistant_context', []);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function putContext(array $data): void
    {
        Session::put('ai_assistant_context', array_filter(array_merge($this->context(), $data), fn ($v) => $v !== null && $v !== ''));
    }

    protected function forgetAwaiting(): void
    {
        $ctx = $this->context();
        unset($ctx['awaiting'], $ctx['pending_intent']);
        Session::put('ai_assistant_context', $ctx);
    }
}
