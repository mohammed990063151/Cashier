@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>@lang('site.expenses')
            <small>@lang('site.add')</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> @lang('site.dashboard')</a></li>
            <li><a href="{{ route('dashboard.expenses.index') }}">@lang('site.expenses')</a></li>
            <li class="active">@lang('site.add')</li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="box box-primary">

                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('site.add_expense')</h3>
                    </div><!-- end of box header -->

                    <div class="box-body">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('dashboard.expenses.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label>@lang('site.title')</label>
                                <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                            </div>

                            <div class="form-group">
                                <label>@lang('site.amount')</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount') }}">
                            </div>

                            <div class="form-group">
                                <label>@lang('site.type')</label>
                                <select name="type" class="form-control" required>
                                    <option value="operational">@lang('site.operational')</option>
                                    <option value="other">@lang('site.other')</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>@lang('site.note')</label>
                                <textarea name="note" class="form-control">{{ old('note') }}</textarea>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> @lang('site.save')</button>
                                <a href="{{ route('dashboard.expenses.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> @lang('site.back')</a>
                            </div>

                        </form>

                    </div><!-- end of box body -->

                </div><!-- end of box -->

            </div><!-- end of col -->
        </div><!-- end of row -->

    </section><!-- end of content -->

</div><!-- end of content-wrapper -->
@endsection
