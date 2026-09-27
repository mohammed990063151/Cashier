<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use App\Support\DecimalMath;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public const CACHE_KEY = 'app.usd_rate';

    public int $repricedProducts = 0;

    /** كم جنيه سوداني يساوي دولاراً واحداً */
    public function rate(): float
    {
        return (float) Cache::remember(self::CACHE_KEY, 300, function () {
            $latest = ExchangeRate::query()->orderByDesc('rate_date')->orderByDesc('id')->value('sdg_per_usd');
            if ($latest && (float) $latest > 0) {
                return (float) $latest;
            }

            $settingRate = Setting::query()->value('usd_rate');

            return (float) ($settingRate > 0 ? $settingRate : 0);
        });
    }

    public function enabled(): bool
    {
        $setting = Setting::query()->first();
        if ($setting && isset($setting->show_usd) && ! $setting->show_usd) {
            return false;
        }

        return $this->rate() > 0;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('app.setting');
    }

    public function toUsd(mixed $sdg): float
    {
        $rate = $this->rate();
        if ($rate <= 0) {
            return 0.0;
        }

        return round((float) $sdg / $rate, 2);
    }

    public function toUsdAt(mixed $sdg, mixed $rate): float
    {
        $divisor = (float) $rate;
        if ($divisor <= 0) {
            return 0.0;
        }

        return round((float) $sdg / $divisor, 4);
    }

    /**
     * مقارنة مبلغ جنيه بين سعر يوم المعاملة والسعر الحالي.
     *
     * @return array{then_usd: float, now_usd: float, loss_usd: float, replacement_sdg: float, gap_sdg: float, split: bool}
     */
    public function compare(float $sdg, float $thenRate, float $nowRate): array
    {
        $thenUsd = $this->toUsdAt($sdg, $thenRate);
        $nowUsd = $this->toUsdAt($sdg, $nowRate);
        $split = $thenRate > 0 && $nowRate > 0 && abs($thenRate - $nowRate) >= 0.5;
        $replacement = $split ? round($thenUsd * $nowRate, 2) : round($sdg, 2);

        return [
            'then_usd' => $thenUsd,
            'now_usd' => $nowUsd,
            'loss_usd' => $split ? round($thenUsd - $nowUsd, 4) : 0.0,
            'replacement_sdg' => $replacement,
            'gap_sdg' => round($replacement - $sdg, 2),
            'split' => $split,
        ];
    }

    /**
     * دين العميل: سعر الاستلام، سعر اليوم، معادل الدولار، والفرق خسارة أو ربح.
     *
     * @param  iterable<int, array{amount: float|int|string, rate?: float|int|string|null}>  $entries
     * @return array{receipt_label: string, today_rate: float, then_usd: float, now_usd: float, loss_usd: float, kind: string}|null
     */
    public function debtChange(iterable $entries): ?array
    {
        $now = $this->rate();
        if ($now <= 0) {
            return null;
        }

        $thenUsd = 0.0;
        $nowUsd = 0.0;
        $rates = [];

        foreach ($entries as $entry) {
            $amount = (float) ($entry['amount'] ?? 0);
            $rate = (float) ($entry['rate'] ?? 0);
            if ($amount <= 0.009) {
                continue;
            }
            $thenRate = $rate > 0 ? $rate : $now;
            $pair = $this->compare($amount, $thenRate, $now);
            $thenUsd += $pair['then_usd'];
            $nowUsd += $pair['now_usd'];
            $rates[] = $thenRate;
        }

        $rates = collect($rates)->filter(fn ($r) => $r > 0)->unique()->sort()->values();
        if ($rates->isEmpty()) {
            return null;
        }

        $loss = round($thenUsd - $nowUsd, 4);
        if (abs($loss) < 0.00005) {
            $loss = 0.0;
        }

        $receipt = $rates->count() === 1
            ? number_format($rates[0], 0)
            : number_format($rates->first(), 0).'–'.number_format($rates->last(), 0);

        return [
            'receipt_label' => $receipt,
            'today_rate' => $now,
            'then_usd' => round($thenUsd, 4),
            'now_usd' => round($nowUsd, 4),
            'loss_usd' => $loss,
            'kind' => $loss > 0 ? 'loss' : ($loss < 0 ? 'gain' : 'flat'),
        ];
    }

    /**
     * @return array<int, array{text: string, title: string, class: string}>
     */
    public function hints(float $sdg, ?float $frozenRate = null): array
    {
        $current = $this->rate();
        $frozen = (float) ($frozenRate ?? 0);
        if (! $this->enabled() && $frozen <= 0) {
            return [];
        }

        $thenRate = $frozen > 0 ? $frozen : $current;
        if ($thenRate <= 0) {
            return [];
        }

        $nowRate = $current > 0 ? $current : $thenRate;
        $pair = $this->compare($sdg, $thenRate, $nowRate);
        $thenLabel = number_format($thenRate, 0);
        $nowLabel = number_format($nowRate, 0);
        if ($pair['split']) {
            return [
                [
                    'text' => 'يومها '.$this->formatUsd($pair['then_usd']).' · 1 $ = '.$thenLabel,
                    'title' => 'هذا المبلغ كان يعادل '.$this->formatUsd($pair['then_usd']).' عندما كان 1 $ = '.$thenLabel.' ج.س',
                    'class' => 'money-usd money-usd-then',
                ],
                [
                    'text' => 'الآن '.$this->formatUsd($pair['now_usd']).' · 1 $ = '.$nowLabel,
                    'title' => 'نفس الجنيه الآن يعادل '.$this->formatUsd($pair['now_usd']).' لأن 1 $ = '.$nowLabel.' ج.س',
                    'class' => 'money-usd',
                ],
            ];
        }

        return [[
            'text' => $this->formatUsd($pair['then_usd']).' · 1 $ = '.$thenLabel,
            'title' => '1 $ = '.$thenLabel.' ج.س',
            'class' => 'money-usd',
        ]];
    }

    public function annotate(float $sdg, ?float $frozenRate = null): string
    {
        $parts = array_map(fn (array $hint) => $hint['text'], $this->hints($sdg, $frozenRate));

        return $parts === [] ? '' : ' '.implode(' ', $parts);
    }

    /**
     * مخزون وديون: قيمة الجنيه ثابتة، والدولار يُقارن بين يوم التسجيل واليوم.
     *
     * @return array{stock: array<string, float>, receivables: array<string, float>, payables: array<string, float>}
     */
    public function holdings(): array
    {
        $now = $this->rate();
        $stockSdg = 0.0;
        $stockThen = 0.0;
        $costSdg = 0.0;
        $costUsd = 0.0;
        $saleSdg = 0.0;
        $saleUsd = 0.0;
        $productColumns = ['stock', 'purchase_price', 'sale_price', 'usd_rate'];
        if (Schema::hasColumn('products', 'sale_usd_rate')) {
            $productColumns[] = 'sale_usd_rate';
        }
        $products = \App\Models\Product::query()
            ->where('stock', '>', 0)
            ->get($productColumns);
        foreach ($products as $product) {
            $rate = (float) $product->usd_rate > 0 ? (float) $product->usd_rate : $now;
            $saleRate = (float) $product->sale_usd_rate > 0 ? (float) $product->sale_usd_rate : $rate;
            $cost = (float) $product->stock * (float) $product->purchase_price;
            $sale = (float) $product->stock * (float) $product->sale_price;
            $costSdg += $cost;
            $saleSdg += $sale;
            $stockSdg += $cost;
            if ($rate > 0) {
                $costUsd += $cost / $rate;
                $stockThen += $cost / $rate;
            }
            if ($saleRate > 0) {
                $saleUsd += $sale / $saleRate;
            }
        }

        $dueSdg = 0.0;
        $dueThen = 0.0;
        $dueRates = [];
        $orders = \App\Models\Order::query()->where('remaining', '>', 0.009)->get(['remaining', 'usd_rate']);
        foreach ($orders as $order) {
            $sdg = (float) $order->remaining;
            $rate = (float) $order->usd_rate > 0 ? (float) $order->usd_rate : $now;
            $dueSdg += $sdg;
            if ($rate > 0) {
                $dueThen += $sdg / $rate;
                $dueRates[] = $rate;
            }
        }

        $oweSdg = 0.0;
        $oweThen = 0.0;
        $oweRates = [];
        $invoices = \App\Models\PurchaseInvoice::query()->where('remaining', '>', 0.009)->get(['remaining', 'usd_rate']);
        foreach ($invoices as $invoice) {
            $sdg = (float) $invoice->remaining;
            $rate = (float) $invoice->usd_rate > 0 ? (float) $invoice->usd_rate : $now;
            $oweSdg += $sdg;
            if ($rate > 0) {
                $oweThen += $sdg / $rate;
                $oweRates[] = $rate;
            }
        }

        $stock = $this->bucket($stockSdg, $stockThen, $now);
        $basisUsd = $saleUsd > 0 ? $saleUsd : $costUsd;
        $basisSdg = $saleUsd > 0 ? $saleSdg : $costSdg;
        $sellNow = ($now > 0 && $basisUsd > 0) ? ($basisUsd * $now) : $basisSdg;
        $stock['cost_sdg'] = round($costSdg, 2);
        $stock['cost_usd'] = round($costUsd, 4);
        $stock['sale_sdg'] = round($basisSdg, 2);
        $stock['sale_usd'] = round($basisUsd, 4);
        $stock['sell_now_sdg'] = round($sellNow, 2);
        $stock['raise_sdg'] = round($sellNow - $basisSdg, 2);

        return [
            'stock' => $stock,
            'receivables' => $this->bucket($dueSdg, $dueThen, $now, $dueRates),
            'payables' => $this->bucket($oweSdg, $oweThen, $now, $oweRates),
        ];
    }

    /**
     * @return array{sdg: float, usd_then: float, usd_now: float, loss_usd: float, replacement_sdg: float, gap_sdg: float}
     */
    protected function bucket(float $sdg, float $usdThen, float $nowRate, array $rates = []): array
    {
        $usdNowRaw = $nowRate > 0 ? $sdg / $nowRate : 0.0;
        $sameRate = $nowRate > 0 && abs($usdThen - $usdNowRaw) < 0.0000005;
        if ($sameRate) {
            $usdThen = $usdNowRaw;
        }
        $replacement = ($nowRate > 0 && ! $sameRate) ? ($usdThen * $nowRate) : $sdg;
        $gap = $replacement - $sdg;
        $rateList = collect($rates)->map(fn ($r) => (float) $r)->filter(fn ($r) => $r > 0)->unique()->sort()->values();
        $rateLabel = '—';
        if ($rateList->count() === 1) {
            $rateLabel = number_format($rateList->first(), 0);
        } elseif ($rateList->count() > 1) {
            $rateLabel = number_format($rateList->first(), 0).'–'.number_format($rateList->last(), 0);
        }

        return [
            'sdg' => round($sdg, 2),
            'usd_then' => $usdThen,
            'usd_now' => $usdNowRaw,
            'loss_usd' => $usdThen - $usdNowRaw,
            'replacement_sdg' => round($replacement, 2),
            'gap_sdg' => round($gap, 2),
            'inflate_pct' => $sdg > 0.009 ? round(($gap / $sdg) * 100, 2) : 0.0,
            'rate_label' => $rateLabel,
            'today_rate' => $nowRate,
        ];
    }

    public function toSdg(mixed $usd): float
    {
        $rate = $this->rate();
        if ($rate <= 0) {
            return 0.0;
        }

        return DecimalMath::money((float) $usd * $rate);
    }

    public function formatSdg(mixed $sdg, int $decimals = 0): string
    {
        return number_format((float) $sdg, $decimals).' ج.س';
    }

    public function formatUsd(mixed $usd): string
    {
        $value = (float) $usd;
        $decimals = abs($value) > 0 && abs($value) < 10 ? 4 : 2;

        return number_format($value, $decimals).' $';
    }

    /** مثال: 45,000 ج.س ≈ 10.00 $ */
    public function dual(mixed $sdg, int $sdgDecimals = 0): string
    {
        $amount = (float) $sdg;
        $sdgText = $this->formatSdg($amount, $sdgDecimals);

        if (! $this->enabled()) {
            return $sdgText;
        }

        return $sdgText.' ≈ '.$this->formatUsd($this->toUsd($amount));
    }

    public function rateLabel(): string
    {
        $rate = $this->rate();
        if ($rate <= 0) {
            return 'لم يُحدَّد سعر الدولار';
        }

        return '1 $ = '.number_format($rate, 0).' ج.س';
    }

    /**
     * حفظ سعر جديد + تحديث الإعدادات
     */
    public function saveRate(float $sdgPerUsd, ?string $rateDate = null, ?string $note = null, ?int $userId = null): ExchangeRate
    {
        $sdgPerUsd = max(0.01, round($sdgPerUsd, 2));
        $rateDate = $rateDate ?: now()->toDateString();

        $row = ExchangeRate::create([
            'sdg_per_usd' => $sdgPerUsd,
            'rate_date' => $rateDate,
            'note' => $note,
            'user_id' => $userId,
        ]);

        $setting = Setting::query()->first();
        if ($setting) {
            $setting->update([
                'usd_rate' => $sdgPerUsd,
                'show_usd' => true,
            ]);
        }

        $this->clearCache();
        $this->repricedProducts = $this->repriceProductSales($sdgPerUsd);

        return $row;
    }

    /**
     * يرفع سعر البيع بنسبة زيادة الدولار، ويُبقي دولار البيع كما كان.
     */
    public function repriceProductSales(float $newRate): int
    {
        $newRate = round($newRate, 2);
        if ($newRate <= 0 || ! Schema::hasColumn('products', 'sale_usd_rate')) {
            return 0;
        }

        $updated = 0;
        Product::withTrashed()
            ->where('sale_price', '>', 0)
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($newRate, &$updated) {
                foreach ($products as $product) {
                    $base = (float) $product->sale_usd_rate;
                    if ($base <= 0) {
                        $base = (float) $product->usd_rate;
                    }
                    if ($base <= 0) {
                        $product->sale_usd_rate = $newRate;
                        $product->saveQuietly();
                        continue;
                    }
                    if ($newRate <= $base + 0.009) {
                        continue;
                    }

                    $next = round((float) $product->sale_price * ($newRate / $base), 3);
                    if (abs($next - (float) $product->sale_price) < 0.0005) {
                        $product->sale_usd_rate = $newRate;
                        $product->saveQuietly();
                        continue;
                    }

                    if (Schema::hasColumn('products', 'previous_sale_price')) {
                        $product->previous_sale_price = $product->sale_price;
                        $product->previous_sale_usd_rate = $base;
                    }
                    $product->sale_price = $next;
                    $product->sale_usd_rate = $newRate;
                    $product->save();
                    $updated++;
                }
            });

        return $updated;
    }
}
