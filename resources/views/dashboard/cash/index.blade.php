@extends('layouts.dashboard.app')

@section('content')

<livewire:dashboard.cash-management />
@endsection
{{--
    <div class="content-wrapper">

        <!-- ======= رأس الصفحة ======= -->
        <section class="content-header">
            <h1>الصندوق
                <small>الرصيد الحالي: {{ number_format($cash->balance, 2) }}</small>
            </h1>

            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
                <li class="active">الصندوق</li>
            </ol>
        </section>

        <!-- ======= المحتوى الرئيسي ======= -->
        <section class="content">

            <div class="row">

                <!-- ======= قائمة الحركات ======= -->
                <div class="col-md-8">
                    <div class="box box-primary">

                        <div class="box-header with-border">
                            <h3 class="box-title">حركات الصندوق</h3>

                            <form action="{{ route('dashboard.cash.filter') }}" method="GET" class="pull-right" style="width: 40%;">
                                <div class="input-group">
                                    <select name="category" class="form-control">
                                        <option value="all">جميع الحركات</option>
                                        <option value="sales">فواتير المبيعات</option>
                                        <option value="returns">فواتير المرتجعات</option>
                                        <option value="purchases">فواتير المشتريات</option>
                                        <option value="clients">سندات العملاء</option>
                                        <option value="suppliers">سندات الموردين</option>
                                        <option value="expenses">المصروفات</option>
                                        <option value="direct">إضافة/سحب نقد مباشر</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-info btn-flat">تصفية</button>
                                    </span>
                                </div>
                            </form>
                        </div>

                        @if ($transactions->count() > 0)
                            <div class="box-body table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>تاريخ الحركة</th>
                                            <th>الوصف</th>
                                            <th>إضافة مبلغ</th>
                                            <th>سحب مبلغ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $trx)
                                            <tr>
                                                <td>{{ $trx->transaction_date }}</td>
                                                <td>{{ $trx->description }}</td>
                                                <td>{{ $trx->type == 'add' ? number_format($trx->amount, 2) : '-' }}</td>
                                                <td>{{ $trx->type == 'deduct' ? number_format($trx->amount, 2) : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $transactions->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="box-body">
                                <h3 class="text-center text-muted">لا توجد أي سجلات حتى الآن</h3>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- ======= إضافة حركة مالية ======= -->
                <div class="col-md-4">
                    <div class="box box-primary">

                        <div class="box-header with-border">
                            <h3 class="box-title">إضافة/سحب مبلغ</h3>
                        </div>

                        <div class="box-body">
                            <form action="{{ route('dashboard.cash.store') }}" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label>نوع الحركة</label>
                                    <select name="type" class="form-control" required>
                                        <option value="add">إضافة مبلغ</option>
                                        <option value="deduct">سحب مبلغ</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>المبلغ</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>الوصف</label>
                                    <input type="text" name="description" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>تاريخ الحركة</label>
                                    <input type="date" name="transaction_date" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-save"></i> حفظ
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>
     --}}

