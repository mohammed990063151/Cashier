@extends('layouts.dashboard.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('dashboard_files/css/client-groups.css') }}">
<style>
.supplier-schedule-page .stat-card { border-radius:10px; padding:16px; color:#fff; text-align:center; margin-bottom:14px; }
.supplier-schedule-page .stat-card h4 { margin:0; font-size:28px; font-weight:800; }
</style>
@endpush

@section('content')
<div class="content-wrapper supplier-schedule-page">
    <section class="content-header">
        <h1 style="font-size:26px;"><i class="fa fa-calendar"></i> جدولة سداد الموردين</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}">لوحة التحكم</a></li>
            <li class="active">سداد الموردين</li>
        </ol>
    </section>

    <section class="content">
        @if(session('success'))<div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif

        <div class="row">
            <div class="col-md-3 col-sm-6"><div class="stat-card" style="background:#e74c3c;"><h4>{{ $summary['overdue'] }}</h4><p>متأخر</p></div></div>
            <div class="col-md-3 col-sm-6"><div class="stat-card" style="background:#f39c12;"><h4>{{ $summary['due_today'] }}</h4><p>مستحق اليوم</p></div></div>
            <div class="col-md-3 col-sm-6"><div class="stat-card" style="background:#3498db;"><h4>{{ $summary['due_soon'] }}</h4><p>خلال {{ $dueSoonDays }} يوم</p></div></div>
            <div class="col-md-3 col-sm-6"><div class="stat-card" style="background:#7f8c8d;"><h4>{{ $summary['no_date'] }}</h4><p>بدون أقساط</p></div></div>
        </div>

        <div class="box box-solid">
            <div class="box-header">
                <h3 class="box-title">ذمم الموردين: <strong class="text-danger">{{ number_format($summary['total_remaining'], 2) }} ج.س</strong></h3>
                <div class="box-tools">
                    <a href="{{ route('dashboard.purchase-invoices.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> فاتورة شراء</a>
                </div>
            </div>
            <div class="box-body" style="padding:20px;">
                <form method="GET" class="client-order-filter-panel" style="margin-bottom:20px;">
                    <div class="row">
                        <div class="col-md-4">
                            <label>المورد</label>
                            <select name="supplier_id" class="form-control input-lg">
                                <option value="">— الكل —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" @selected(($filters['supplier_id'] ?? '') == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>بحث</label>
                            <input type="text" name="search" class="form-control input-lg" value="{{ $filters['search'] ?? '' }}" placeholder="رقم الفاتورة أو المورد">
                        </div>
                        <div class="col-md-4">
                            <label>الحالة</label>
                            <select name="schedule_status" class="form-control input-lg">
                                @foreach(['all'=>'الكل','overdue'=>'متأخر','due_today'=>'اليوم','alert'=>'تنبيه','no_date'=>'بدون جدولة'] as $k=>$lbl)
                                    <option value="{{ $k }}" @selected(($filters['schedule_status'] ?? 'all') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="margin-top:12px;"><i class="fa fa-filter"></i> عرض</button>
                </form>

                @forelse($supplierGroups as $group)
                <div class="client-group-card">
                    <div class="client-group-summary">
                        <div class="client-group-info">
                            <div class="client-avatar"><i class="fa fa-truck"></i></div>
                            <div>
                                <h3 class="client-group-name">{{ $group['supplier']->name }}</h3>
                                <span class="badge bg-blue">{{ $group['invoices_count'] }} فاتورة مفتوحة</span>
                            </div>
                        </div>
                        <div class="client-group-totals">
                            <div class="total-remaining-box">
                                <small>إجمالي المتبقي</small>
                                <strong>{{ number_format($group['total_remaining'], 2) }}</strong> ج.س
                            </div>
                        </div>
                    </div>
                    <div class="client-orders-inner" style="padding:12px 16px;">
                        @foreach($group['invoices'] as $invoice)
                            @include('dashboard.supplier-schedules._invoice_row', ['invoice' => $invoice])
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="empty-state-box"><p>لا توجد فواتير مستحقة — أو غيّر الفلتر.</p></div>
                @endforelse

                @if(method_exists($supplierGroups, 'links'))
                    <div class="text-center">{{ $supplierGroups->links() }}</div>
                @endif
            </div>
        </div>
    </section>
</div>

@include('dashboard.supplier-schedules._installment_modal')
@endsection

@push('scripts')
<script src="{{ asset('dashboard_files/js/custom/supplier-schedule.js') }}?v=1"></script>
@endpush
