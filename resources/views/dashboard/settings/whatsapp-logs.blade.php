@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>سجل رسائل واتساب</h1>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-body table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>الوقت</th>
                            <th>المستلم</th>
                            <th>الطلب</th>
                            <th>الرقم المدخل</th>
                            <th>الرقم بعد التصحيح</th>
                            <th>الحالة</th>
                            <th>السبب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $item)
                            <tr>
                                <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($item->recipient === 'client') العميل
                                    @elseif($item->recipient === 'staff') المستخدم
                                    @else اختبار
                                    @endif
                                </td>
                                <td>{{ $item->order?->order_number ?: '—' }}</td>
                                <td>{{ $item->phone ?: '—' }}</td>
                                <td>{{ $item->phone_normalized ?: '—' }}</td>
                                <td>
                                    @if($item->status === 'success')
                                        <span class="label label-success">نجحت</span>
                                    @else
                                        <span class="label label-danger">فشلت</span>
                                    @endif
                                </td>
                                <td>{{ $item->reason ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">لا توجد رسائل بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $messages->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
