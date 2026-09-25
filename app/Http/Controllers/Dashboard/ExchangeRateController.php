<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\Setting;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index(CurrencyService $currency)
    {
        $rates = ExchangeRate::with('user:id,first_name,last_name')
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->paginate(20);

        $current = $currency->rate();
        $setting = Setting::first();

        return view('dashboard.exchange_rates.index', [
            'rates' => $rates,
            'currentRate' => $current,
            'showUsd' => (bool) ($setting->show_usd ?? true),
            'rateLabel' => $currency->rateLabel(),
        ]);
    }

    public function store(Request $request, CurrencyService $currency)
    {
        $data = $request->validate([
            'sdg_per_usd' => 'required|numeric|min:1',
            'rate_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
            'show_usd' => 'nullable|boolean',
        ], [
            'sdg_per_usd.required' => 'أدخل كم جنيه يساوي دولاراً واحداً.',
            'sdg_per_usd.min' => 'السعر يجب أن يكون أكبر من صفر.',
        ]);

        $currency->saveRate(
            (float) $data['sdg_per_usd'],
            $data['rate_date'] ?? now()->toDateString(),
            $data['note'] ?? null,
            auth()->id()
        );

        if (array_key_exists('show_usd', $data) || $request->has('show_usd')) {
            $setting = Setting::first();
            if ($setting) {
                $setting->update(['show_usd' => $request->boolean('show_usd')]);
                $currency->clearCache();
            }
        }

        return redirect()
            ->route('dashboard.exchange-rates.index')
            ->with('success', 'تم حفظ سعر الدولار: 1 $ = '.number_format((float) $data['sdg_per_usd'], 0).' ج.س');
    }

    public function convert(Request $request, CurrencyService $currency)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from' => 'required|in:sdg,usd',
        ]);

        $amount = (float) $data['amount'];
        $rate = $currency->rate();

        if ($rate <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'حدّد سعر الدولار أولاً من صفحة العملة.',
            ], 422);
        }

        if ($data['from'] === 'sdg') {
            $usd = $currency->toUsd($amount);

            return response()->json([
                'ok' => true,
                'from' => 'sdg',
                'input' => $amount,
                'sdg' => $amount,
                'usd' => $usd,
                'rate' => $rate,
                'text' => number_format($amount, 0).' ج.س ≈ '.number_format($usd, 2).' $',
            ]);
        }

        $sdg = $currency->toSdg($amount);

        return response()->json([
            'ok' => true,
            'from' => 'usd',
            'input' => $amount,
            'sdg' => $sdg,
            'usd' => $amount,
            'rate' => $rate,
            'text' => number_format($amount, 2).' $ ≈ '.number_format($sdg, 0).' ج.س',
        ]);
    }
}
