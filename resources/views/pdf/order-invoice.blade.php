<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: dejavusans, sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            direction: rtl;
            background: #fff;
            line-height: 1.45;
        }
        .receipt {
            width: 100%;
            max-width: 72mm;
            margin: 0 auto;
            padding: 0 1mm;
        }

        /* ── فواصل مثل إيصالات المولات ── */
        .rule-double {
            border: none;
            border-top: 2px solid #1a1a1a;
            border-bottom: 1px solid #1a1a1a;
            height: 3px;
            margin: 6px 0;
        }
        .rule-dashed {
            border: none;
            border-top: 1px dashed #999;
            margin: 5px 0;
        }
        .rule-dotted {
            border: none;
            border-top: 1px dotted #bbb;
            margin: 4px 0;
        }

        /* ── الترويسة ── */
        .head-center { text-align: center; padding: 2px 0 4px; }
        .head-center img {
            max-height: 42px;
            max-width: 60mm;
            margin-bottom: 4px;
        }
        .store-name {
            font-size: 13pt;
            font-weight: bold;
            color: #111;
            letter-spacing: 0.3px;
            margin: 2px 0;
        }
        .receipt-type {
            font-size: 10pt;
            color: #444;
            margin: 2px 0 6px;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
            color: #fff;
            margin: 4px 0;
        }
        .status-paid { background: #15803d; }
        .status-partial { background: #c2410c; }
        .status-unpaid { background: #b91c1c; }
        .status-returned { background: #0369a1; }
        .status-partial_return { background: #7c3aed; }
        .ret-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin: 6px 0; }
        .ret-table th, .ret-table td { border: 1px solid #ddd; padding: 3px 4px; }
        .ret-table th { background: #f5f5f5; }
        .acct-line { font-size: 8pt; padding: 2px 0; }
        .acct-line strong { float: left; direction: ltr; }

        .store-contact {
            font-size: 7.5pt;
            color: #555;
            text-align: center;
            line-height: 1.5;
            margin-top: 3px;
        }

        /* ── بيانات الطلب ── */
        .meta-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        .meta-table td { padding: 2px 0; vertical-align: top; }
        .meta-table .meta-label { color: #666; width: 38%; }
        .meta-table .meta-value { font-weight: bold; text-align: left; direction: ltr; }

        /* ── المنتجات ── */
        .section-label {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            color: #555;
            letter-spacing: 1px;
            margin: 4px 0 6px;
        }
        .item-block {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dotted #ddd;
        }
        .item-block:last-child { border-bottom: none; }
        .item-name {
            font-size: 10pt;
            font-weight: bold;
            color: #111;
            margin-bottom: 4px;
        }
        .unit-line {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            color: #333;
            margin-bottom: 2px;
        }
        .unit-line td { padding: 1px 0; }
        .unit-line .u-label { color: #444; }
        .unit-line .u-qty { font-weight: bold; }
        .unit-line .u-amt {
            text-align: left;
            direction: ltr;
            font-weight: bold;
            color: #111;
            white-space: nowrap;
        }
        .item-subtotal {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            font-size: 8.5pt;
        }
        .item-subtotal td { padding: 2px 0; }
        .item-subtotal .pieces-info { color: #666; font-size: 7.5pt; }
        .item-subtotal .line-total {
            text-align: left;
            direction: ltr;
            font-weight: bold;
            font-size: 10pt;
            color: #111;
        }

        /* ── الإجماليات ── */
        .totals-wrap {
            background: #f7f7f7;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 6px 8px;
            margin: 6px 0;
        }
        .totals-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        .totals-table td { padding: 3px 0; }
        .totals-table .t-label { color: #444; }
        .totals-table .t-value {
            text-align: left;
            direction: ltr;
            font-weight: bold;
        }
        .totals-table tr.grand-total td {
            padding-top: 6px;
            border-top: 2px solid #1a1a1a;
            font-size: 12pt;
            font-weight: bold;
            color: #111;
        }
        .totals-table .paid-val { color: #15803d; }
        .totals-table .remain-val { color: #b91c1c; }
        .totals-table .zero-val { color: #15803d; }

        /* ── التذييل ── */
        .footer {
            text-align: center;
            padding: 8px 0 4px;
        }
        .footer-thanks {
            font-size: 11pt;
            font-weight: bold;
            color: #111;
            margin-bottom: 4px;
        }
        .footer-note {
            font-size: 7.5pt;
            color: #888;
            line-height: 1.5;
        }
        .barcode-deco {
            text-align: center;
            font-size: 14pt;
            letter-spacing: 2px;
            color: #ccc;
            margin: 6px 0 2px;
            font-family: dejavusansmono, monospace;
        }
    </style>
</head>
<body>
@php
    $fin = app(\App\Services\OrderFinancialService::class);
    $status = $fin->paymentStatus($order);
    $statusLabel = $fin->paymentStatusLabel($status);
@endphp

<div class="receipt">

    {{-- ═══ الترويسة ═══ --}}
    <div class="head-center">
        @if(!empty($logoPath))
            <img src="{{ $logoPath }}" alt="">
        @endif
        <div class="store-name">{{ $setting->name ?? 'إيصال مبيعات' }}</div>
        <div class="receipt-type">إيصال مبيعات</div>
        <span class="status-pill status-{{ $status }}">{{ $statusLabel }}</span>
        @if($setting && ($setting->phone || $setting->address))
        <div class="store-contact">
            @if($setting->phone){{ $setting->phone }}@endif
            @if($setting->phone && $setting->address) · @endif
            @if($setting->address){{ $setting->address }}@endif
        </div>
        @endif
    </div>

    <div class="rule-double"></div>

    {{-- ═══ بيانات الطلب ═══ --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">رقم الطلب</td>
            <td class="meta-value">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td class="meta-label">التاريخ</td>
            <td class="meta-value">{{ $order->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">الوقت</td>
            <td class="meta-value">{{ $order->created_at->format('H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">العميل</td>
            <td class="meta-value" style="direction:rtl;text-align:right;">{{ $order->client->name }}</td>
        </tr>
    </table>

    <div class="rule-dashed"></div>
    <div class="section-label">— تفاصيل المشتريات —</div>

    {{-- ═══ المنتجات ═══ --}}
    @foreach ($order->products as $product)
            @php
                $breakdown = $fin->productUnitBreakdown($product);
                $saleLine = $fin->formatProductSaleLine($product);
                $lineTotal = $saleLine['line_total'];
                $qtyLabel = $fin->formatProductQuantity($product);
            @endphp
        <div class="item-block">
            <div class="item-name">{{ $product->name }}</div>

            @if(count($breakdown) > 0)
                @foreach($breakdown as $line)
                <table class="unit-line">
                    <tr>
                        <td class="u-label">{{ $line['label'] }}</td>
                        <td class="u-qty" style="width:18%;text-align:center;">× {{ $line['count'] }}</td>
                        <td class="u-amt" style="width:32%;">{{ number_format($line['line_total'], 2) }}</td>
                    </tr>
                </table>
                @endforeach
            @endif

            <table class="item-subtotal">
                <tr>
                    <td class="pieces-info">{{ $qtyLabel }} — {{ $saleLine['price'] }}</td>
                    <td class="line-total" style="width:38%;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    @if($hasReturns ?? false)
    <div class="rule-dashed"></div>
    <div class="section-label">— المرتجعات —</div>
    @foreach($order->returns->sortByDesc('return_date') as $ret)
    <div style="font-size:8pt;margin-bottom:6px;padding:4px;border:1px dashed #ccc;">
        <strong>{{ $ret->return_number }}</strong> — {{ $ret->return_date->format('d/m/Y') }}<br>
        قيمة بضاعة: {{ number_format($ret->items_total, 2) }}
        @if($ret->refund_amount > 0)
        | مُسترد نقداً: {{ number_format($ret->refund_amount, 2) }}
        @endif
        @if($ret->items->isNotEmpty())
        <table class="ret-table">
            @foreach($ret->items as $item)
            <tr>
                <td>{{ $item->product->name ?? '—' }}</td>
                <td style="text-align:center;">{{ $item->quantity }}</td>
                <td style="text-align:center;">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    </div>
    @endforeach
    @endif

    <div class="rule-double"></div>

    {{-- ═══ الإجماليات ═══ --}}
    <div class="totals-wrap">
        <table class="totals-table">
            @if($hasReturns ?? false)
            <tr>
                <td class="t-label">إجمالي البيع الأصلي</td>
                <td class="t-value">{{ number_format($originalTotalSale, 2) }}</td>
            </tr>
            <tr>
                <td class="t-label">إجمالي المرتجعات</td>
                <td class="t-value" style="color:#b91c1c;">- {{ number_format($totalReturnedMerchandise, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="t-label">{{ ($hasReturns ?? false) ? 'صافي المبيعات الحالي' : 'إجمالي المبيعات' }}</td>
                <td class="t-value">{{ number_format($totalSale, 2) }}</td>
            </tr>
            @if($invoiceDiscount > 0)
            <tr>
                <td class="t-label">خصم الفاتورة</td>
                <td class="t-value" style="color:#c2410c;">- {{ number_format($invoiceDiscount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="t-label">بعد الخصم</td>
                <td class="t-value">{{ number_format($totalAfterDiscount, 2) }}</td>
            </tr>
            <tr>
                <td class="t-label">إجمالي المدفوع</td>
                <td class="t-value paid-val">{{ number_format($totalPaid, 2) }}</td>
            </tr>
            @if(($totalRefundedToCustomer ?? 0) > 0)
            <tr>
                <td class="t-label">مُسترد للعميل (خزينة)</td>
                <td class="t-value" style="color:#b91c1c;">- {{ number_format($totalRefundedToCustomer, 2) }}</td>
            </tr>
            <tr>
                <td class="t-label">صافي المدفوع</td>
                <td class="t-value paid-val">{{ number_format($netPaid ?? max(0, $totalPaid - $totalRefundedToCustomer), 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="t-label">المتبقي</td>
                <td class="t-value {{ $remaining > 0 ? 'remain-val' : 'zero-val' }}">{{ number_format($remaining, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td class="t-label">الإجمالي النهائي</td>
                <td class="t-value">{{ number_format($totalAfterDiscount, 2) }} ج.س</td>
            </tr>
        </table>
    </div>

    <div class="rule-dashed"></div>

    {{-- ═══ التذييل ═══ --}}
    <div class="footer">
        <div class="footer-thanks">شكراً لتعاملكم معنا</div>
        <div class="footer-note">
            هذا الإيصال صادر إلكترونياً ولا يحتاج توقيعاً<br>
            {{ $order->order_number }} · {{ $order->created_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <div class="barcode-deco">▌▌▌▌ ▌ ▌▌▌▌ ▌ ▌▌▌▌</div>
    <div class="rule-double"></div>

</div>
</body>
</html>
