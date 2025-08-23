@extends('layouts.dashboard.app')

@section('content')

    <div class="content-wrapper">

        <!-- ======= Header ======= -->
        <section class="content-header">
            <h1>@lang('site.cash')
                <small>@lang('site.current_balance'): {{ number_format($cash->balance, 2) }}</small>
            </h1>

            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> @lang('site.dashboard')</a></li>
                <li class="active">@lang('site.cash')</li>
            </ol>
        </section>

        <!-- ======= Main Content ======= -->
        <section class="content">

            <div class="row">

                <!-- ======= Transactions List ======= -->
                <div class="col-md-8">
                    <div class="box box-primary">

                        <div class="box-header with-border">
                            <h3 class="box-title">@lang('site.cash_movements')</h3>

                            <form action="{{ route('dashboard.cash.filter') }}" method="GET" class="pull-right" style="width: 40%;">
                                <div class="input-group">
                                    <select name="category" class="form-control">
                                        <option value="all">@lang('site.all_transactions')</option>
                                        <option value="sales">@lang('site.sales_invoices')</option>
                                        <option value="returns">@lang('site.return_invoices')</option>
                                        <option value="purchases">@lang('site.purchase_invoices')</option>
                                        <option value="clients">@lang('site.client_vouchers')</option>
                                        <option value="suppliers">@lang('site.supplier_vouchers')</option>
                                        <option value="expenses">@lang('site.expenses')</option>
                                        <option value="direct">@lang('site.direct_cash')</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-info btn-flat">@lang('site.filter')</button>
                                    </span>
                                </div>
                            </form>
                        </div>

                        @if ($transactions->count() > 0)
                            <div class="box-body table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>@lang('site.transaction_date')</th>
                                            <th>@lang('site.description')</th>
                                            <th>@lang('site.add_amount')</th>
                                            <th>@lang('site.deduct_amount')</th>
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
                                <h3>@lang('site.no_records')</h3>
                            </div>
                        @endif

                    </div>
                </div>

                <!-- ======= Add Transaction ======= -->
                <div class="col-md-4">
                    <div class="box box-primary">

                        <div class="box-header with-border">
                            <h3 class="box-title">@lang('site.add_amount') / @lang('site.deduct_amount')</h3>
                        </div>

                        <div class="box-body">
                            <form action="{{ route('dashboard.cash.store') }}" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label>@lang('site.type')</label>
                                    <select name="type" class="form-control" required>
                                        <option value="add">@lang('site.add_amount')</option>
                                        <option value="deduct">@lang('site.deduct_amount')</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>@lang('site.amount')</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>@lang('site.description')</label>
                                    <input type="text" name="description" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>@lang('site.transaction_date')</label>
                                    <input type="date" name="transaction_date" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-save"></i> @lang('site.save')
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection
