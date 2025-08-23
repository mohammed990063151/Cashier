@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <!-- Content Header -->
    <section class="content-header">
        <h1>
            إعدادات الخزينة
        </h1>

        <ol class="breadcrumb">
            <li>
                <a href="{{ route('dashboard.welcome') }}">
                    <i class="fa fa-dashboard"></i> @lang('site.dashboard')
                </a>
            </li>
            <li class="active">إعدادات الخزينة</li>
        </ol>
    </section>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="row">

            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">

                    <div class="box-header">
                        <h3 class="box-title">تعديل إعدادات الخزينة</h3>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('dashboard.cash.update.settings') }}" method="POST">
                        @csrf

                        <div class="box-body">

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="add_sales" {{ $settings->add_sales ? 'checked' : '' }}>
                                        إضافة مبالغ البيع للخزينة
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="add_client_payments" {{ $settings->add_client_payments ? 'checked' : '' }}>
                                        إضافة سداد العملاء للخزينة
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="deduct_purchases" {{ $settings->deduct_purchases ? 'checked' : '' }}>
                                        خصم المشتريات من الخزينة
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="deduct_supplier_payments" {{ $settings->deduct_supplier_payments ? 'checked' : '' }}>
                                        خصم سداد الموردين من الخزينة
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="deduct_expenses" {{ $settings->deduct_expenses ? 'checked' : '' }}>
                                        خصم المصروفات من الخزينة
                                    </label>
                                </div>
                            </div>

                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                    <!-- /.form -->

                </div><!-- /.box -->
            </div><!-- /.col -->

        </div><!-- /.row -->
    </section>
    <!-- /.content -->

</div><!-- /.content-wrapper -->

@endsection
