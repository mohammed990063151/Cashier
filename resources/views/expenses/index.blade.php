@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>@lang('site.expenses')
            <small>{{ $expenses->total() }} @lang('site.expenses')</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> @lang('site.dashboard')</a></li>
            <li class="active">@lang('site.expenses')</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title" style="margin-bottom: 10px">@lang('site.expenses')</h3>

                        <div class="mb-3 text-end">
                            <a href="{{ route('dashboard.expenses.create') }}" class="btn btn-primary">➕ @lang('site.add_expense')</a>
                        </div>

                    </div><!-- end of box header -->

                    <div class="box-body table-responsive">

                        @if(session('success'))
                            <div class="alert alert-success text-center">{{ session('success') }}</div>
                        @endif

                        <table class="table table-hover table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('site.title')</th>
                                    <th>@lang('site.amount')</th>
                                    <th>@lang('site.type')</th>
                                    <th>@lang('site.note')</th>
                                    <th>@lang('site.created_at')</th>
                                    <th>@lang('site.action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                    <tr>
                                        <td>{{ $expense->id }}</td>
                                        <td>{{ $expense->title }}</td>
                                        <td class="text-danger fw-bold">{{ number_format($expense->amount, 2) }} ر.س</td>
                                        <td>
                                            <span class="badge {{ $expense->type == 'operational' ? 'bg-warning' : 'bg-secondary' }}">
                                                {{ $expense->type == 'operational' ? __('site.operational') : __('site.other') }}
                                            </span>
                                        </td>
                                        <td>{{ $expense->note ?? '-' }}</td>
                                        <td>{{ $expense->created_at->toFormattedDateString() }}</td>
                                        <td class="d-flex justify-content-center gap-1">

                                            <a href="{{ route('dashboard.expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fa fa-pencil"></i> @lang('site.edit')
                                            </a>

                                            <form action="{{ route('dashboard.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('@lang('site.confirm_delete')');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> @lang('site.delete')</button>
                                            </form>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted">@lang('site.no_records')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center mt-2">
                            {{ $expenses->links() }}
                        </div>

                    </div><!-- end of box body -->

                </div><!-- end of box -->

            </div><!-- end of col -->

        </div><!-- end of row -->

    </section><!-- end of content -->

</div><!-- end of content-wrapper -->
@endsection
