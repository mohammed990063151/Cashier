<?php

/**
 * Smoke tests for core POS logic (units, stock, orders, AI).
 * Run: php tests_smoke_logic.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Payment;
use App\Models\Product;
use App\Services\AiAssistant\AssistantEngine;
use App\Services\OrderFinancialService;
use App\Services\OrderLineNormalizer;
use App\Services\ProductService;
use App\Support\DecimalMath;
use App\Support\SaleUnits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$failed = 0;
$passed = 0;

function assert_true(bool $cond, string $msg): void
{
    global $failed, $passed;
    if ($cond) {
        echo "PASS  {$msg}\n";
        $passed++;
    } else {
        echo "FAIL  {$msg}\n";
        $failed++;
    }
}

DB::beginTransaction();

try {
    $cat = Category::first() ?? Category::create(['name' => 'اختبار']);

    // 1) Carton entry normalization
    $entry = SaleUnits::normalizeProductEntry([
        'measure_unit' => 'carton',
        'sale_mode' => 'flexible',
        'pieces_per_carton' => 12,
        'purchase_price' => 120,
        'sale_price' => 180,
        'stock' => 2.5,
    ]);
    assert_true($entry['purchase_price'] === 10.0, 'carton purchase/piece = 10');
    assert_true($entry['sale_price'] === 15.0, 'carton sale/piece = 15');
    // 2.5 كرتونة تُقرَّب إلى 3 كراتين صحيحة → 36 حبة
    assert_true($entry['stock'] === 36.0, 'carton stock pieces = 36 (2.5→3 cartons)');
    assert_true($entry['sale_mode'] === 'flexible', 'carton defaults flexible sale mode');

    // 2) Kilo entry
    $kilo = SaleUnits::normalizeProductEntry([
        'measure_unit' => 'kilo',
        'sale_mode' => 'flexible',
        'pieces_per_carton' => 12,
        'purchase_price' => 10.455,
        'sale_price' => 15.999,
        'stock' => 1.455,
    ]);
    assert_true($kilo['measure_unit'] === 'kilo', 'kilo measure kept');
    assert_true($kilo['sale_mode'] === 'piece_only', 'kilo forces piece_only');
    assert_true($kilo['pieces_per_carton'] === 1, 'kilo bulk=1');
    assert_true($kilo['stock'] === 1.455, 'kilo stock 3 decimals');

    // 3) Units for carton flexible
    $units = SaleUnits::unitsForOrderForm(24, 'flexible', 'carton');
    assert_true(isset($units['piece'], $units['half_carton'], $units['bulk']), 'carton units: piece/half/bulk');
    assert_true((float) $units['half_carton']['multiplier'] === 12.0, 'half of 24 = 12');
    assert_true((float) $units['bulk']['multiplier'] === 24.0, 'full carton = 24');

    // 4) toPieceLine conversion — المال من سعر الوحدة المدخل، المخزون بالحبة
    $line = SaleUnits::toPieceLine([
        'piece' => ['qty' => 2, 'price' => 15],
        'half_carton' => ['qty' => 1, 'price' => 180],
        'bulk' => ['qty' => 1, 'price' => 360],
    ], 24, 'flexible', 'carton');
    // 2 + 12 + 24 = 38 pieces; money = 2*15 + 180 + 360 = 570
    assert_true($line['quantity'] === 38.0, 'toPieceLine qty=38');
    assert_true(abs($line['line_total'] - 570) < 0.01, 'toPieceLine line_total=570');
    assert_true(abs($line['sale_price'] - (570 / 38)) < 0.0001, 'toPieceLine unit avg');

    // 4b) 5 كراتين × 7 = 35 بالضبط (بدون 34.98)
    $cartonMoney = SaleUnits::toPieceLine([
        'bulk' => ['qty' => 5, 'price' => 7],
    ], 12, 'bulk_only', 'carton');
    assert_true($cartonMoney['quantity'] === 60.0, '5 cartons = 60 pieces stock');
    assert_true($cartonMoney['line_total'] === 35.0, '5×7 carton money = 35');
    assert_true(abs($cartonMoney['line_total'] - 34.98) > 0.01, 'not 34.98 drift');

    // 4c) كيلو: 1.5 × 10 = 15
    $kiloMoney = SaleUnits::toPieceLine([
        'kilo' => ['qty' => 1.5, 'price' => 10],
    ], 1, 'piece_only', 'kilo');
    assert_true($kiloMoney['quantity'] === 1.5, 'kilo qty 1.5');
    assert_true($kiloMoney['line_total'] === 15.0, 'kilo line_total 15');

    // 4d) حبة فقط: 10 × 8 = 80
    $pieceMoney = SaleUnits::toPieceLine([
        'piece' => ['qty' => 10, 'price' => 8],
    ], 1, 'piece_only', 'piece');
    assert_true($pieceMoney['line_total'] === 80.0, 'piece line_total 80');

    // 4e) سعر الكرتونة من line_total: 35 على 60 حبة = 7 للكرتونة
    $fromLine = SaleUnits::unitPriceFromLineMoney('bulk', 35.0, 60.0, 12, 'carton');
    assert_true($fromLine === 7.0, 'unitPriceFromLineMoney carton = 7');
    $cartonBreak = SaleUnits::breakdownLines(60, 12, 'bulk_only', 'carton');
    assert_true(($cartonBreak[0]['label'] ?? '') === 'كرتونة (12 حبة)', 'breakdown uses كرتونة not دستة');
    assert_true((int) ($cartonBreak[0]['count'] ?? 0) === 5, 'breakdown 5 cartons');

    // 5) Create real products and sell
    $pieceProduct = Product::create([
        'category_id' => $cat->id,
        'name' => 'TEST-PIECE-'.uniqid(),
        'purchase_price' => 5,
        'sale_price' => 8,
        'stock' => 10,
        'pieces_per_carton' => 1,
        'sale_mode' => 'piece_only',
        'measure_unit' => 'piece',
        'image' => Product::DEFAULT_IMAGE,
    ]);

    $cartonProduct = Product::create(array_merge(
        ['category_id' => $cat->id, 'name' => 'TEST-CARTON-'.uniqid(), 'image' => Product::DEFAULT_IMAGE],
        SaleUnits::normalizeProductEntry([
            'measure_unit' => 'carton',
            'sale_mode' => 'flexible',
            'pieces_per_carton' => 12,
            'purchase_price' => 120,
            'sale_price' => 180,
            'stock' => 2, // 2 cartons = 24 pieces
        ])
    ));

    $kiloProduct = Product::create(array_merge(
        ['category_id' => $cat->id, 'name' => 'TEST-KILO-'.uniqid(), 'image' => Product::DEFAULT_IMAGE],
        SaleUnits::normalizeProductEntry([
            'measure_unit' => 'kilo',
            'purchase_price' => 10,
            'sale_price' => 14,
            'stock' => 5.5,
            'pieces_per_carton' => 1,
            'sale_mode' => 'piece_only',
        ])
    ));

    assert_true((float) $cartonProduct->stock === 24.0, 'created carton stock=24 pieces');
    assert_true((float) $kiloProduct->stock === 5.5, 'created kilo stock=5.5');

    $svc = app(ProductService::class);
    assert_true(str_contains($svc->stockDisplay($cartonProduct), 'كرتونة'), 'stockDisplay shows carton');

    // 6) OrderLineNormalizer — valid sale (1 carton = 12 <= 24)
    $req = Request::create('/test', 'POST', [
        'products' => [
            $cartonProduct->id => [
                'bulk' => ['qty' => 1, 'price' => 180],
                'piece' => ['qty' => 0, 'price' => 15],
                'half_carton' => ['qty' => 0, 'price' => 90],
            ],
        ],
    ]);
    $normalized = app(OrderLineNormalizer::class)->fromRequest($req);
    assert_true(isset($normalized[$cartonProduct->id]), 'normalizer returns carton product');
    assert_true($normalized[$cartonProduct->id]['quantity'] === 12.0, '1 carton => 12 pieces');

    // 7) Over-stock detection via SaleUnits calc
    $over = SaleUnits::toPieceLine([
        'bulk' => ['qty' => 3, 'price' => 180],
    ], 12, 'flexible', 'carton');
    assert_true($over['quantity'] === 36.0, '3 cartons => 36 pieces');
    assert_true($over['quantity'] > (float) $cartonProduct->stock, '36 > 24 stock correctly over');

    // 8) Simulate stock deduction
    $before = (float) $cartonProduct->stock;
    $sell = 12.0;
    $cartonProduct->update(['stock' => DecimalMath::sub($before, $sell)]);
    $cartonProduct->refresh();
    assert_true((float) $cartonProduct->stock === 12.0, 'stock after sell 1 carton = 12');

    // 9) Kilo fractional sale
    $kiloLine = SaleUnits::toPieceLine([
        'kilo' => ['qty' => 1.255, 'price' => 14],
    ], 1, 'piece_only', 'kilo');
    assert_true($kiloLine['quantity'] === 1.255, 'kilo qty preserved');
    assert_true($kiloLine['quantity'] <= (float) $kiloProduct->stock, 'kilo sale within stock');

    // 10) Exact stock boundary (should allow equal)
    $exact = SaleUnits::toPieceLine([
        'piece' => ['qty' => 10, 'price' => 8],
    ], 1, 'piece_only', 'piece');
    assert_true($exact['quantity'] === 10.0, 'exact 10 pieces');
    assert_true($exact['quantity'] <= (float) $pieceProduct->stock + 0.0005, 'equal stock allowed');

    // 11) Return money: net paid = paid - refund; remaining uses net
    $client = Client::first() ?? Client::create([
        'name' => 'TEST-CLIENT-'.uniqid(),
        'phone' => '000',
        'address' => 'test',
    ]);
    $returnOrder = Order::create([
        'client_id' => $client->id,
        'order_number' => 'TST-'.uniqid(),
        'total_price' => 50,
        'paid_at_sale' => 80,
        'invoice_discount' => 0,
        'total_after_discount' => 50,
        'remaining' => 0,
        'total_return' => 50,
        'profit' => 0,
    ]);
    $returnOrder->products()->attach($pieceProduct->id, [
        'quantity' => 5,
        'sale_price' => 10,
        'cost_price' => 5,
    ]);
    Payment::create([
        'order_id' => $returnOrder->id,
        'amount' => 80,
        'method' => 'cash_at_sale',
        'notes' => 'test',
    ]);
    OrderReturn::create([
        'order_id' => $returnOrder->id,
        'return_number' => 'RET-TST-'.uniqid(),
        'items_total' => 50,
        'refund_amount' => 30,
        'remaining_reduced' => 20,
        'return_date' => now()->toDateString(),
    ]);
    $finCalc = app(OrderFinancialService::class)->calculate($returnOrder->fresh(['products', 'payments', 'returns']));
    assert_true(abs($finCalc['totalPaid'] - 80) < 0.01, 'gross paid stays 80');
    assert_true(abs($finCalc['netPaid'] - 50) < 0.01, 'net paid = 80-30 = 50');
    assert_true(abs($finCalc['totalAfterDiscount'] - 50) < 0.01, 'net total after return = 50');
    assert_true(abs($finCalc['remaining']) < 0.01, 'remaining 0 after refund');
    assert_true(abs(($finCalc['netPaid'] + $finCalc['remaining']) - $finCalc['totalAfterDiscount']) < 0.02, 'netPaid+remaining≈total');

    // 12) AI assistant replies with real data
    $ai = app(AssistantEngine::class);
    $r1 = $ai->handle('ملخص المخزون');
    assert_true(str_contains($r1['reply'], 'ملخص المخزون'), 'AI inventory summary');
    $r2 = $ai->handle('إنشاء طلب');
    assert_true(! empty($r2['links']), 'AI create order has links');
    $r3 = $ai->handle('عرض التقارير');
    assert_true(count($r3['links']) > 0, 'AI reports links');

    // 13) DecimalMath rounding
    assert_true(DecimalMath::mul(1.455, 15.999) === 23.279, 'mul 3 decimals');
    assert_true(DecimalMath::div(120.455, 12) === 10.038, 'div 3 decimals');

    // 14) Entry values round-trip for carton
    $roundTrip = SaleUnits::toEntryValues($cartonProduct->fresh(), 'carton');
    assert_true(abs($roundTrip['sale_price'] - 180) < 0.01, 'entry sale price back to carton');
    assert_true(abs($roundTrip['stock'] - 1.0) < 0.01, 'entry stock back to 1 carton after sale');

    echo "\nResult: {$passed} passed, {$failed} failed\n";
} catch (Throwable $e) {
    echo 'EXCEPTION: '.$e->getMessage()."\n".$e->getFile().':'.$e->getLine()."\n";
    $failed++;
} finally {
    DB::rollBack();
    echo "DB rolled back (no persistent test data).\n";
}

exit($failed > 0 ? 1 : 0);
