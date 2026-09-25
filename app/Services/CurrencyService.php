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
        return number_format((float) $usd, 2).' $';
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
