@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>الطلبات المحذوفة</h1>
    </section>

    <section class="content">

        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">الطلبات المحذوفة</h3>
            </div>

            <div class="box-body table-responsive">

                @if($orders->count() > 0)
                <table class="table table-hover">
                    <tr>
                        <th>رقم الطلب</th>
                        <th>اسم العميل</th>
                        <th>إجمالي الطلب</th>
                        <th>تاريخ الحذف</th>
                        <th>الإجراءات</th>
                    </tr>

                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->client->name }}</td>
                        <td>{{ number_format($order->total_price, 2) }}</td>
                        <td>{{ $order->deleted_at->toFormattedDateString() }}</td>
                        <td>
                            {{-- @if (auth()->user()->hasPermission('restore_orders')) --}}
                                <form action="{{ route('dashboard.orders.restore', $order->id) }}" method="post" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="fa fa-undo"></i> استرجاع
                                    </button>
                                </form>
                            {{-- @endif --}}
                        </td>
                    </tr>
                    @endforeach
                </table>

                {{ $orders->links() }}

                @else
                <p>لا توجد طلبات محذوفة.</p>
                @endif

            </div>
        </div>

    </section>
</div>
@endsection
