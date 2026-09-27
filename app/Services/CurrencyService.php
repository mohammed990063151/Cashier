<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Setting;
use App\Support\DecimalMath;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public const CACHE_KEY = 'app.usd_rate';

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

        $pair = $this->compare($sdg, $thenRate, $current > 0 ? $current : $thenRate);
        if ($pair['split']) {
            return [
                [
                    'text' => '≈ '.$this->formatUsd($pair['then_usd']).' يومها',
                    'title' => 'سعر يوم المعاملة: 1 $ = '.number_format($frozen, 0).' ج.س',
                    'class' => 'money-usd money-usd-then',
                ],
                [
                    'text' => '≈ '.$this->formatUsd($pair['now_usd']).' الآن',
                    'title' => $this->rateLabel(),
                    'class' => 'money-usd',
                ],
            ];
        }

        return [[
            'text' => '≈ '.$this->formatUsd($pair['then_usd']),
            'title' => '1 $ = '.number_format($thenRate, 0).' ج.س',
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
        $products = \App\Models\Product::query()
            ->where('stock', '>', 0)
            ->get(['stock', 'purchase_price', 'usd_rate']);
        foreach ($products as $product) {
            $sdg = (float) $product->stock * (float) $product->purchase_price;
            $rate = (float) $product->usd_rate > 0 ? (float) $product->usd_rate : $now;
            $stockSdg += $sdg;
            if ($rate > 0) {
                $stockThen += $sdg / $rate;
            }
        }

        $dueSdg = 0.0;
        $dueThen = 0.0;
        $orders = \App\Models\Order::query()->where('remaining', '>', 0.009)->get(['remaining', 'usd_rate']);
        foreach ($orders as $order) {
            $sdg = (float) $order->remaining;
            $rate = (float) $order->usd_rate > 0 ? (float) $order->usd_rate : $now;
            $dueSdg += $sdg;
            if ($rate > 0) {
                $dueThen += $sdg / $rate;
            }
        }

        $oweSdg = 0.0;
        $oweThen = 0.0;
        $invoices = \App\Models\PurchaseInvoice::query()->where('remaining', '>', 0.009)->get(['remaining', 'usd_rate']);
        foreach ($invoices as $invoice) {
            $sdg = (float) $invoice->remaining;
            $rate = (float) $invoice->usd_rate > 0 ? (float) $invoice->usd_rate : $now;
            $oweSdg += $sdg;
            if ($rate > 0) {
                $oweThen += $sdg / $rate;
            }
        }

        return [
            'stock' => $this->bucket($stockSdg, $stockThen, $now),
            'receivables' => $this->bucket($dueSdg, $dueThen, $now),
            'payables' => $this->bucket($oweSdg, $oweThen, $now),
        ];
    }

    /**
     * @return array{sdg: float, usd_then: float, usd_now: float, loss_usd: float, replacement_sdg: float, gap_sdg: float}
     */
    protected function bucket(float $sdg, float $usdThen, float $nowRate): array
    {
        $usdNowRaw = $nowRate > 0 ? $sdg / $nowRate : 0.0;
        $sameRate = $nowRate > 0 && abs($usdThen - $usdNowRaw) < 0.0000005;
        if ($sameRate) {
            $usdThen = $usdNowRaw;
        }
        $replacement = ($nowRate > 0 && ! $sameRate) ? ($usdThen * $nowRate) : $sdg;

        return [
            'sdg' => round($sdg, 2),
            'usd_then' => round($usdThen, 4),
            'usd_now' => round($usdNowRaw, 4),
            'loss_usd' => round($usdThen - $usdNowRaw, 4),
            'replacement_sdg' => round($replacement, 2),
            'gap_sdg' => round($replacement - $sdg, 2),
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

        return $row;
    }
}
