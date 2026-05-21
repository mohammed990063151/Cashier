<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة شراء {{ $purchaseInvoice->invoice_number }}</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            direction: rtl;
            font-size: 9pt;
            color: #1a1a2e;
            margin: 0;
            padding: 0;
        }
        table { border-collapse: collapse; width: 100%; }
        td, th { vertical-align: middle; }

        /* ── Header ── */
        .hdr { margin-bottom: 6px; }
        .hdr td { padding: 0; border: none; }
        .hdr-bar { background: #0f4c81; height: 4px; }
        .hdr-main td { padding: 8px 10px; border: 1px solid #cbd5e1; }
        .hdr-brand { background: #f8fafc; width: 62%; }
        .hdr-doc { background: #0f4c81; color: #fff; text-align: center; width: 38%; }
        .company { font-size: 13pt; font-weight: bold; color: #0f4c81; }
        .sub { font-size: 7.5pt; color: #64748b; }
        .doc-title { font-size: 14pt; font-weight: bold; }
        .doc-no { font-size: 10pt; margin-top: 2px; }
        .logo { max-height: 38px; max-width: 90px; }

        /* ── Meta strip ── */
        .meta { margin-bottom: 6px; font-size: 8pt; }
        .meta td { padding: 5px 8px; border: 1px solid #e2e8f0; background: #f1f5f9; }
        .meta-lbl { color: #64748b; width: 72px; }
        .meta-val { font-weight: bold; }

        /* ── Items ── */
        .items { margin-bottom: 6px; }
        .items th {
            background: #0f4c81;
            color: #fff;
            padding: 5px 4px;
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            border: 1px solid #0a3d66;
        }
        .items td {
            padding: 4px 4px;
            font-size: 8.5pt;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .items tr.alt td { background: #f8fafc; }
        .items .name { text-align: right; font-weight: bold; }
        .items .num { font-weight: bold; }

        /* ── Summary rows in table footer ── */
        .sum td {
            padding: 5px 8px;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #e2e8f0;
        }
        .sum-grand td { background: #0f4c81; color: #fff; font-size: 10pt; }
        .sum-paid td { background: #ecfdf5; color: #047857; }
        .sum-due td { background: #fef2f2; color: #b91c1c; }
        .sum-lbl { text-align: right; width: 70%; }
        .sum-val { text-align: left; width: 30%; }

        /* ── Bottom: notes + signatures ── */
        .bottom td { vertical-align: top; padding: 0; border: none; }
        .notes-cell { width: 58%; padding-left: 6px; }
        .sig-cell-wrap { width: 42%; }
        .notes-box {
            border: 1px solid #fde68a;
            background: #fffbeb;
            padding: 6px 8px;
            font-size: 7.5pt;
            color: #92400e;
            line-height: 1.35;
        }
        .inst-line { font-size: 7.5pt; color: #0f4c81; margin-top: 4px; font-weight: bold; }
        .sig-table td { text-align: center; padding: 4px 6px; border: 1px solid #e2e8f0; width: 50%; }
        .sig-h { font-size: 7.5pt; color: #64748b; padding-bottom: 18px; }
        .sig-line { border-top: 1px solid #94a3b8; font-size: 7pt; color: #475569; padding-top: 3px; }

        .foot {
            margin-top: 4px;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>
<body>

@php
    $logoPath = ($setting && $setting->logo) ? public_path('storage/'.$setting->logo) : public_path('logo.png');
    $hasLogo = file_exists($logoPath);
    $invoiceDate = optional($purchaseInvoice->invoice_date)->format('d/m/Y') ?? $purchaseInvoice->created_at->format('d/m/Y');
    $unpaidInst = $purchaseInvoice->paymentInstallments?->whereNull('paid_at') ?? collect();
    $itemCount = $purchaseInvoice->items->count();
@endphp

{{-- Header --}}
<table class="hdr">
    <tr><td colspan="2" class="hdr-bar"></td></tr>
    <tr class="hdr-main">
        <td class="hdr-brand">
            <table width="100%"><tr>
                @if($hasLogo)
                <td width="95" style="border:none;padding:0 0 0 8px;">
                    <img src="{{ $logoPath }}" class="logo" alt="">
                </td>
                @endif
                <td style="border:none;padding:0;">
                    <div class="company">{{ $setting->name ?? 'شركة عتاب التجارية' }}</div>
                    <div class="sub">فاتورة شراء تجارية — Purchase Invoice</div>
                </td>
            </tr></table>
        </td>
        <td class="hdr-doc">
            <div class="doc-title">فاتورة شراء</div>
            <div class="doc-no">{{ $purchaseInvoice->invoice_number }}</div>
            <div style="font-size:8pt;margin-top:4px;opacity:.9;">{{ $invoiceDate }}</div>
        </td>
    </tr>
</table>

{{-- Meta: supplier + invoice facts in one row --}}
<table class="meta">
    <tr>
        <td class="meta-lbl">المورد</td>
        <td class="meta-val">{{ $purchaseInvoice->supplier->name ?? '—' }}</td>
        <td class="meta-lbl">الهاتف</td>
        <td class="meta-val">{{ $purchaseInvoice->supplier->phone ?? '—' }}</td>
        <td class="meta-lbl">التاريخ</td>
        <td class="meta-val">{{ $invoiceDate }}</td>
        <td class="meta-lbl">الأصناف</td>
        <td class="meta-val">{{ $itemCount }}</td>
    </tr>
    @if($purchaseInvoice->supplier->address ?? null)
    <tr>
        <td class="meta-lbl">العنوان</td>
        <td class="meta-val" colspan="7">{{ $purchaseInvoice->supplier->address }}</td>
    </tr>
    @endif
</table>

{{-- Items + financial summary in one table --}}
<table class="items">
    <thead>
        <tr>
            <th width="22">#</th>
            <th width="28%">الصنف</th>
            <th width="14%">الوحدة</th>
            <th width="8%">الكمية</th>
            <th width="12%">حبات</th>
            <th width="12%">السعر</th>
            <th width="14%">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchaseInvoice->items as $index => $item)
        @php
            $unitLabel = $item->purchase_unit_label ?? 'حبة';
            $entered = $item->entered_qty ?? $item->quantity;
        @endphp
        <tr class="{{ $index % 2 ? 'alt' : '' }}">
            <td>{{ $index + 1 }}</td>
            <td class="name">{{ $item->product->name ?? '—' }}</td>
            <td>{{ $unitLabel }}</td>
            <td>{{ $entered }}</td>
            <td>{{ number_format($item->quantity) }}</td>
            <td>{{ number_format($item->price, 2) }}</td>
            <td class="num">{{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="sum sum-grand">
            <td colspan="5" class="sum-lbl">إجمالي الفاتورة</td>
            <td colspan="2" class="sum-val">{{ number_format($purchaseInvoice->total, 2) }} ج.س</td>
        </tr>
        <tr class="sum sum-paid">
            <td colspan="5" class="sum-lbl">المدفوع</td>
            <td colspan="2" class="sum-val">{{ number_format($purchaseInvoice->paid, 2) }} ج.س</td>
        </tr>
        <tr class="sum sum-due">
            <td colspan="5" class="sum-lbl">المتبقي على المورد</td>
            <td colspan="2" class="sum-val">{{ number_format($purchaseInvoice->remaining, 2) }} ج.س</td>
        </tr>
    </tfoot>
</table>

{{-- Notes + signatures side by side --}}
<table class="bottom" width="100%">
    <tr>
        <td class="notes-cell">
            <div class="notes-box">
                @if($purchaseInvoice->payment_notes)
                    <strong>ملاحظات:</strong> {{ $purchaseInvoice->payment_notes }} —
                @endif
                فحص البضاعة عند الاستلام. المرتجعات خلال 24 ساعة بإذن مسبق.
            </div>
            @if($unpaidInst->count())
            <div class="inst-line">
                جدولة السداد:
                @foreach($unpaidInst as $inst)
                    {{ number_format($inst->amount, 2) }} ({{ $inst->due_at->format('d/m/Y') }})@if(!$loop->last) · @endif
                @endforeach
            </div>
            @endif
        </td>
        <td class="sig-cell-wrap">
            <table class="sig-table" width="100%">
                <tr>
                    <td>
                        <div class="sig-h">توقيع وختم المورد</div>
                        <div class="sig-line">الاسم: ..................</div>
                    </td>
                    <td>
                        <div class="sig-h">توقيع المستلم</div>
                        <div class="sig-line">الاسم: ..................</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="foot">{{ $purchaseInvoice->invoice_number }} — {{ now()->format('d/m/Y H:i') }}</div>

</body>
</html>
