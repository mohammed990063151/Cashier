@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>سجل معاملات العملاء</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li><a href="{{ route('dashboard.clients.index') }}">العملاء</a></li>
            <li class="active">السجل</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">بحث وتصفية</h3>
            </div>
            <div class="box-body">
                <form method="GET" class="row client-history-filters">
                    <div class="col-sm-3 col-xs-12 form-group">
                        <label>رقم الطلب</label>
                        <input type="text" name="order_number" value="{{ $filters['order_number'] }}" class="form-control" placeholder="SU-xxxxx">
                    </div>
                    <div class="col-sm-3 col-xs-12 form-group">
                        <label>اسم العميل</label>
                        <input type="text" name="client" value="{{ $filters['client'] }}" class="form-control" placeholder="بحث بالاسم">
                    </div>
                    <div class="col-sm-3 col-xs-12 form-group">
                        <label>عميل محدد</label>
                        <select name="client_id" class="form-control">
                            <option value="">— الكل —</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" @selected((int)$filters['client_id'] === (int)$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3 col-xs-12 form-group">
                        <label>حالة الدفع</label>
                        <select name="status" class="form-control">
                            <option value="all" @selected($filters['status']==='all')>الكل</option>
                            <option value="unpaid" @selected($filters['status']==='unpaid')>غير مدفوع</option>
                            <option value="partial" @selected($filters['status']==='partial')>جزئي</option>
                            <option value="paid" @selected($filters['status']==='paid')>مدفوع</option>
                        </select>
                    </div>
                    <div class="col-xs-12 form-group">
                        <label>
                            <input type="checkbox" name="unpaid_only" value="1" @checked($filters['unpaid_only'])>
                            عرض الطلبات التي عليها مبالغ فقط
                        </label>
                    </div>
                    <div class="col-xs-12">
                        <button class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                        <a href="{{ route('dashboard.clients.history') }}" class="btn btn-default">إعادة</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">المعاملات (من الأقدم للأحدث)</h3>
            </div>
            <div class="box-body table-responsive mobile-card-table">
                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td data-label="التاريخ">{{ $row['order']->created_at->format('Y-m-d H:i') }}</td>
                                <td data-label="رقم الطلب"><strong>{{ $row['order']->order_number }}</strong></td>
                                <td data-label="العميل">{{ $row['client_name'] }}</td>
                                <td data-label="الإجمالي" style="color:#01941f;font-weight:bold;">{{ number_format($row['total'], 2) }}</td>
                                <td data-label="المدفوع" style="color:#2980b9;font-weight:bold;">{{ number_format($row['paid'], 2) }}</td>
                                <td data-label="المتبقي" style="color:#e74c3c;font-weight:bold;">{{ number_format($row['remaining'], 2) }}</td>
                                <td data-label="الحالة"><span class="label {{ $row['status_class'] }}">{{ $row['status_label'] }}</span></td>
                                <td data-label="التفاصيل">
                                    <button type="button" class="btn btn-xs btn-info" data-toggle="collapse" data-target="#hist-{{ $row['order']->id }}">
                                        منتجات
                                    </button>
                                    <a class="btn btn-xs btn-success" href="{{ route('dashboard.payments.index', ['client_id' => $row['order']->client_id, 'order_id' => $row['order']->id]) }}">دفعة</a>
                                </td>
                            </tr>
                            <tr class="collapse" id="hist-{{ $row['order']->id }}">
                                <td colspan="8" class="text-right">
                                    @forelse($row['products'] as $line)
                                        <div>• {{ $line['quantity'] }} — {{ $line['price'] }} — إجمالي {{ number_format($line['line_total'], 2) }} ج.س</div>
                                    @empty
                                        <div class="text-muted">لا توجد منتجات</div>
                                    @endforelse
                                    @if($row['discount'] > 0)
                                        <div class="text-warning">خصم الفاتورة: {{ number_format($row['discount'], 2) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8">لا توجد معاملات مطابقة</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $orders->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
