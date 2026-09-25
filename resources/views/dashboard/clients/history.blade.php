@extends('layouts.dashboard.app')

@section('content')
@include('dashboard.clients._history_styles')

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
                <form method="GET" class="row client-history-filters mobile-filter-panel">
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

        @if($grand['count'] === 0)
            <div class="box box-solid">
                <div class="box-body text-center text-muted" style="padding:28px;">
                    لا توجد معاملات مطابقة
                </div>
            </div>
        @else
            @foreach($groups as $group)
                <div class="box history-status-group">
                    <div class="box-header with-border history-group-head">
                        <h3 class="box-title">
                            <span class="label {{ $group['status_class'] }}">{{ $group['status_label'] }}</span>
                            <small class="history-group-count">{{ $group['count'] }} فاتورة</small>
                        </h3>
                    </div>
                    <div class="box-body" style="padding:0;">
                        <div class="table-responsive mobile-card-table">
                            <table class="table table-bordered table-striped text-center history-group-table" style="margin:0;">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>الإجمالي</th>
                                        <th>المدفوع</th>
                                        <th>المتبقي</th>
                                        <th>التفاصيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group['rows'] as $row)
                                        <tr>
                                            <td data-label="التاريخ">{{ $row['order']->created_at->format('Y-m-d H:i') }}</td>
                                            <td data-label="رقم الطلب"><strong>{{ $row['order']->order_number }}</strong></td>
                                            <td data-label="العميل">{{ $row['client_name'] }}</td>
                                            <td data-label="الإجمالي" class="money-total">{{ number_format($row['total'], 2) }}</td>
                                            <td data-label="المدفوع" class="money-paid">{{ number_format($row['paid'], 2) }}</td>
                                            <td data-label="المتبقي" class="money-remain">{{ number_format($row['remaining'], 2) }}</td>
                                            <td data-label="التفاصيل">
                                                <div class="phone-action-bar history-row-actions">
                                                    <button type="button" class="btn btn-info btn-sm" data-toggle="collapse" data-target="#hist-{{ $row['order']->id }}">
                                                        <i class="fa fa-cubes"></i> منتجات
                                                    </button>
                                                    <a class="btn btn-success btn-sm" href="{{ route('dashboard.payments.index', ['client_id' => $row['order']->client_id, 'order_id' => $row['order']->id]) }}">
                                                        <i class="fa fa-money"></i> دفعة
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="collapse history-products-row" id="hist-{{ $row['order']->id }}">
                                            <td colspan="7" class="text-right history-products-cell">
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
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="history-group-total">
                                        <td colspan="3" data-label="ملخص المجموعة"><strong>إجمالي {{ $group['status_label'] }}</strong></td>
                                        <td data-label="مجموع الإجمالي" class="money-total"><strong>{{ number_format($group['total'], 2) }}</strong></td>
                                        <td data-label="مجموع المدفوع" class="money-paid"><strong>{{ number_format($group['paid'], 2) }}</strong></td>
                                        <td data-label="مجموع المتبقي" class="money-remain"><strong>{{ number_format($group['remaining'], 2) }}</strong></td>
                                        <td data-label="عدد الفواتير"><strong>{{ $group['count'] }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="box box-success history-grand-box">
                <div class="box-body">
                    <div class="history-grand-title">الإجمالي العام</div>
                    <div class="history-grand-grid">
                        <div class="history-grand-item">
                            <span>عدد الفواتير</span>
                            <strong>{{ $grand['count'] }}</strong>
                        </div>
                        <div class="history-grand-item">
                            <span>الإجمالي</span>
                            <strong class="money-total"><x-money :amount="$grand['total']" :decimals="0" /></strong>
                        </div>
                        <div class="history-grand-item">
                            <span>المدفوع</span>
                            <strong class="money-paid"><x-money :amount="$grand['paid']" :decimals="0" /></strong>
                        </div>
                        <div class="history-grand-item">
                            <span>المتبقي</span>
                            <strong class="money-remain"><x-money :amount="$grand['remaining']" :decimals="0" /></strong>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
