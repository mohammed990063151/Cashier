@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>فواتير الشراء</h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li class="active">فواتير الشراء</li>
        </ol>
    </section>

    <section class="content">

        <div class="box box-primary">

            <div class="box-header with-border">
                <h3 class="box-title">قائمة الفواتير <small>{{ $purchaseInvoices->total() }}</small></h3>

                <form action="{{ route('dashboard.purchase-invoices.index') }}" method="get">
                    <div class="row" style="margin-top: 10px">

                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="بحث باسم المورد أو رقم الفاتورة" value="{{ request()->search }}">
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>

                            {{-- @if (auth()->user()->hasPermission('create_purchase_invoices')) --}}
                                <a href="{{ route('dashboard.purchase-invoices.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> إضافة</a>
                            {{-- @else
                                <button class="btn btn-primary disabled"><i class="fa fa-plus"></i> إضافة</button>
                            @endif --}}
                        </div>

                    </div>
                </form>

            </div><!-- /.box-header -->

            <div class="box-body">

                @if ($purchaseInvoices->count() > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المورد</th>
                                <th>التاريخ</th>
                                <th>الإجمالي</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseInvoices as $index => $invoice)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $invoice->supplier->name ?? 'غير معروف' }}</td>
                                    <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                    <td>{{ number_format($invoice->total, 2) }}</td>
                                    <td>
    {{-- زر عرض --}}
    <a href="{{ route('dashboard.purchase-invoices.show', $invoice->id) }}" class="btn btn-info btn-sm">
        <i class="fa fa-eye"></i> عرض
    </a>

    {{-- زر طباعة --}}
    <a href="{{ route('dashboard.purchase-invoices.print', $invoice->id) }}" class="btn btn-default btn-sm" target="_blank">
        <i class="fa fa-print"></i> طباعة
    </a>

    {{-- زر تعديل --}}
    {{-- @if (auth()->user()->hasPermission('update_purchase_invoices')) --}}
        <a href="{{ route('dashboard.purchase-invoices.edit', $invoice->id) }}" class="btn btn-primary btn-sm">
            <i class="fa fa-edit"></i> تعديل
        </a>
    {{-- @else
        <a href="#" class="btn btn-primary btn-sm disabled">
            <i class="fa fa-edit"></i> تعديل
        </a>
    @endif --}}

    {{-- زر حذف --}}
    {{-- @if (auth()->user()->hasPermission('delete_purchase_invoices')) --}}
        <form action="{{ route('dashboard.purchase-invoices.destroy', $invoice->id) }}" method="post" style="display: inline-block">
            @csrf
            @method('delete')
            <button type="submit" class="btn btn-danger btn-sm delete">
                <i class="fa fa-trash"></i> حذف
            </button>
        </form>
    {{-- @else
        <button class="btn btn-danger btn-sm disabled"><i class="fa fa-trash"></i> حذف</button>
    @endif --}}
</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $purchaseInvoices->appends(request()->query())->links() }}

                @else
                    <h4>لا توجد فواتير.</h4>
                @endif

            </div><!-- /.box-body -->

        </div><!-- /.box -->

    </section><!-- /.content -->

</div><!-- /.content-wrapper -->

@endsection
