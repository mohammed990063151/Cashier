@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>الهوالك / الديون المعدومة</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">الديون المعدومة</li>
        </ol>
    </section>

    <section class="content">
        @include('reports._debt_status')

        <div class="alert alert-danger text-center">
            <strong>إجمالي الديون المعدومة:</strong>
            <x-report-money :amount="$summary['bad_amount']" :entries="$summary['bad_entries']" />
            — {{ $summary['bad_count'] }} طلب من {{ $summary['bad_clients'] }} عميل.
            هذه المبالغ خرجت من التحصيل ومن ذمم العملاء، والمبيعات نفسها بقيت في تقارير البيع.
        </div>

        <div class="box box-danger">
            <div class="box-body table-responsive">
                @if($orders->isEmpty())
                    <p class="text-center text-muted" style="margin:20px 0;">لا توجد ديون معدومة حتى الآن.</p>
                @else
                <table class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>الطلب</th>
                            <th>العميل</th>
                            <th>المبلغ المعدوم</th>
                            <th>التاريخ</th>
                            <th>السبب</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('dashboard.orders.show', $order->id) }}">{{ $order->order_number }}</a>
                                @if($order->is_opening_balance)
                                    <div><span class="label label-warning">حساب قديم</span></div>
                                @endif
                            </td>
                            <td>{{ $order->client->name ?? '—' }}</td>
                            <td><x-report-money :amount="$order->written_off_amount" :rate="$order->usd_rate" /></td>
                            <td>{{ optional($order->written_off_at)->format('Y-m-d') }}</td>
                            <td>{{ $order->written_off_note ?: '—' }}</td>
                            <td>
                                @if(auth()->user()->hasPermission('update_orders'))
                                <form method="post" action="{{ route('dashboard.bad-debts.restore', $order) }}" onsubmit="return confirm('إعادة هذا الطلب إلى الذمم المستحقة؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm" style="min-height:36px;">
                                        <i class="fa fa-undo"></i> إعادة للتحصيل
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
