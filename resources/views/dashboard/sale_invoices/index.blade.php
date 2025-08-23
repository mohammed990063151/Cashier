@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>فواتير البيع
            <small>{{ $invoices->total() }} فاتورة</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li class="active">فواتير البيع</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-header">

                        <h3 class="box-title" style="margin-bottom: 10px">فواتير البيع</h3>

                        <form action="{{ route('dashboard.sale_invoices.index') }}" method="get">

                            <div class="row">

                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="بحث" value="{{ request()->search }}">
                                </div>

                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> بحث</button>
                                </div>

                            </div><!-- end of row -->

                        </form><!-- end of form -->

                    </div><!-- end of box header -->

                    @if ($invoices->count() > 0)

                        <div class="box-body table-responsive">

                            <table class="table table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم العميل</th>
                                        <th>تاريخ الفاتورة</th>
                                        <th>الإجمالي</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice->id }}</td>
                                            <td>{{ $invoice->client->name ?? '-' }}</td>
                                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                            <td>{{ number_format($invoice->total_amount, 2) }} ر.س</td>
                                            <td>
                                                <a href="{{ route('dashboard.sale-invoices.show', $invoice->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> عرض</a>

                                                <a href="{{ route('dashboard.sale-invoices.print', $invoice->id) }}" class="btn btn-success btn-sm"><i class="fa fa-print"></i> طباعة</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            {{ $invoices->appends(request()->query())->links() }}

                        </div>

                    @else

                        <div class="box-body text-center">
                            <h3>لا توجد فواتير حالياً</h3>
                        </div>

                    @endif

                </div><!-- end of box -->

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content section -->

</div><!-- end of content wrapper -->

@endsection
