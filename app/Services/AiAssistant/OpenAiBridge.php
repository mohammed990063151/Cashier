<?php

namespace App\Services\AiAssistant;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

class OpenAiBridge
{
    public function __construct(protected AssistantTools $tools)
    {
    }

    public function enabled(): bool
    {
        return filled(config('services.openai.key'));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function reply(string $message): ?array
    {
        if (! $this->enabled() || trim($message) === '') {
            return null;
        }

        $history = Session::get('ai_assistant_llm_messages', []);
        $history[] = ['role' => 'user', 'content' => $message];
        $history = array_slice($history, -8);

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $history
        );

        $products = [];
        $links = [];

        try {
            for ($round = 0; $round < 4; $round++) {
                $response = $this->chat($messages);
                if ($response === null) {
                    return null;
                }

                $choice = $response['choices'][0]['message'] ?? [];
                $toolCalls = $choice['tool_calls'] ?? [];

                if ($toolCalls === []) {
                    $text = trim((string) ($choice['content'] ?? ''));
                    if ($text === '') {
                        return null;
                    }

                    $history[] = ['role' => 'assistant', 'content' => $text];
                    Session::put('ai_assistant_llm_messages', array_slice($history, -8));

                    return [
                        'reply' => $text,
                        'suggestions' => [
                            ['label' => '📦 مخزون منتج', 'text' => 'كم مخزون'],
                            ['label' => '📊 ملخص المخزون', 'text' => 'ملخص المخزون'],
                            ['label' => '💰 مبيعات اليوم', 'text' => 'مبيعات اليوم'],
                            ['label' => '⚠️ نواقص', 'text' => 'المنتجات الناقصة'],
                        ],
                        'products' => $products,
                        'product_actions' => $products === [] ? [] : [
                            ['key' => 'stock', 'label' => 'المخزون'],
                            ['key' => 'value', 'label' => 'القيمة'],
                            ['key' => 'carton', 'label' => 'كراتين'],
                        ],
                        'links' => $links,
                        'meta' => ['source' => 'openai'],
                    ];
                }

                $messages[] = [
                    'role' => 'assistant',
                    'content' => $choice['content'] ?? null,
                    'tool_calls' => $toolCalls,
                ];

                foreach ($toolCalls as $call) {
                    $name = (string) ($call['function']['name'] ?? '');
                    $args = json_decode((string) ($call['function']['arguments'] ?? '{}'), true);
                    if (! is_array($args)) {
                        $args = [];
                    }

                    $result = $this->executeTool($name, $args);
                    $this->collectUi($result, $products, $links);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $call['id'] ?? uniqid('tool_'),
                        'content' => json_encode($this->forModel($result), JSON_UNESCAPED_UNICODE),
                    ];
                }
            }
        } catch (Throwable $e) {
            Log::warning('OpenAI assistant failed', ['error' => $e->getMessage()]);

            return null;
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<string, mixed>|null
     */
    protected function chat(array $messages): ?array
    {
        $response = Http::withToken((string) config('services.openai.key'))
            ->acceptJson()
            ->timeout(30)
            ->post(rtrim((string) config('services.openai.base_url'), '/').'/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'temperature' => 0.2,
                'messages' => $messages,
                'tools' => $this->toolDefinitions(),
                'tool_choice' => 'auto',
            ]);

        if (! $response->successful()) {
            Log::warning('OpenAI HTTP error', [
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 400),
            ]);

            return null;
        }

        return $response->json();
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
أنت مساعد عربي لنظام كاشير/مخزون. تجيب باختصار ووضوح.
لا تخترع أرقاماً: استخدم الأدوات دائماً لأي مخزون أو مبيعات أو عملاء أو تقارير.
إذا تطابقت عدة منتجات، اذكرها واطلب التحديد.
الأرقام بالتقويم الحالي للعملة ج.س. لا تذكر أنك نموذج لغوي إلا إذا سُئلت.
PROMPT;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function toolDefinitions(): array
    {
        $string = ['type' => 'string'];
        $number = ['type' => 'number'];

        return [
            $this->fn('search_products', 'البحث عن منتجات بالاسم', ['query' => $string]),
            $this->fn('product_exists', 'التحقق من وجود منتج', ['query' => $string], ['query']),
            $this->fn('inventory_summary', 'ملخص المخزون الكلي'),
            $this->fn('low_stock', 'المنتجات منخفضة المخزون', ['threshold' => $number]),
            $this->fn('out_of_stock', 'المنتجات النافدة'),
            $this->fn('sales_summary', 'ملخص المبيعات', ['from' => $string, 'to' => $string]),
            $this->fn('clients_summary', 'ملخص العملاء والمديونية', ['query' => $string]),
            $this->fn('suppliers_summary', 'ملخص الموردين والمشتريات'),
            $this->fn('expenses_summary', 'ملخص المصروفات', ['from' => $string, 'to' => $string]),
            $this->fn('cash_balance', 'رصيد الخزنة'),
            $this->fn('categories_summary', 'ملخص الأقسام'),
            $this->fn('find_report', 'البحث عن تقرير أو رابط شاشة', ['query' => $string], ['query']),
            $this->fn('recent_orders', 'آخر الطلبات'),
            $this->fn('create_order_link', 'رابط إنشاء طلب / بيع مباشر'),
            $this->fn('calculate_quantity', 'حساب قيمة كمية لمنتج', [
                'product_id' => ['type' => 'integer'],
                'qty' => $number,
                'unit' => $string,
            ], ['product_id', 'qty']),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $properties
     * @param  array<int, string>  $required
     * @return array<string, mixed>
     */
    protected function fn(string $name, string $description, array $properties = [], array $required = []): array
    {
        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];
        if ($required !== []) {
            $schema['required'] = $required;
        }

        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => $schema,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return mixed
     */
    protected function executeTool(string $name, array $args): mixed
    {
        return match ($name) {
            'search_products' => $this->tools->searchProducts($args['query'] ?? null)->values()->all(),
            'product_exists' => $this->tools->productExists((string) ($args['query'] ?? '')),
            'inventory_summary' => $this->tools->inventorySummary(),
            'low_stock' => $this->tools->lowStockProducts((int) ($args['threshold'] ?? 10))->values()->all(),
            'out_of_stock' => $this->tools->outOfStockProducts()->values()->all(),
            'sales_summary' => $this->tools->salesSummary($args['from'] ?? null, $args['to'] ?? null),
            'clients_summary' => $this->clientsPayload($args['query'] ?? null),
            'suppliers_summary' => $this->tools->suppliersSummary(),
            'expenses_summary' => $this->tools->expensesSummary($args['from'] ?? null, $args['to'] ?? null),
            'cash_balance' => $this->tools->cashBalance(),
            'categories_summary' => $this->tools->categoriesSummary()->values()->all(),
            'find_report' => $this->tools->findReport((string) ($args['query'] ?? '')),
            'recent_orders' => $this->tools->recentOrders()->values()->all(),
            'create_order_link' => [[
                'title' => 'إنشاء طلب سريع',
                'url' => route('dashboard.direct-sale'),
            ]],
            'calculate_quantity' => $this->calculate((int) ($args['product_id'] ?? 0), (float) ($args['qty'] ?? 1), (string) ($args['unit'] ?? 'piece')),
            default => ['error' => 'unknown_tool'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function clientsPayload(?string $query): array
    {
        $summary = $this->tools->clientsSummary();
        if (filled($query)) {
            $summary['matches'] = $this->tools->searchClients($query)->values()->all();
        }

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    protected function calculate(int $productId, float $qty, string $unit): array
    {
        $product = Product::find($productId);
        if (! $product) {
            return ['error' => 'product_not_found'];
        }

        return $this->tools->calculateQuantityValue($product, $qty, $unit ?: 'piece');
    }

    /**
     * @param  mixed  $result
     * @param  array<int, array<string, mixed>>  $products
     * @param  array<int, array{label: string, url: string}>  $links
     */
    protected function collectUi(mixed $result, array &$products, array &$links): void
    {
        $rows = [];
        if (isset($result['products']) && is_array($result['products'])) {
            $rows = $result['products'];
        } elseif (is_array($result) && array_is_list($result)) {
            $rows = $result;
        }

        foreach ($rows as $row) {
            if (is_array($row) && isset($row['id'], $row['name'], $row['stock_display'])) {
                $products[] = $row;
            }
            if (is_array($row) && isset($row['url'], $row['title'])) {
                $links[] = ['label' => $row['title'], 'url' => $row['url']];
            }
        }

        $products = array_values(array_unique($products, SORT_REGULAR));
        $links = array_values(array_unique($links, SORT_REGULAR));
    }

    protected function forModel(mixed $result): mixed
    {
        if (! is_array($result)) {
            return $result;
        }

        if (array_is_list($result)) {
            return array_map(fn ($row) => is_array($row) ? Arr::except($row, ['image']) : $row, $result);
        }

        if (isset($result['products']) && is_array($result['products'])) {
            $result['products'] = array_map(
                fn ($row) => is_array($row) ? Arr::except($row, ['image']) : $row,
                $result['products']
            );
        }

        return $result;
    }
}
