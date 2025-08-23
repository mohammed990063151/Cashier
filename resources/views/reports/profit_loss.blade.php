@extends('layouts.dashboard.app')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <h1>تقرير الأرباح والخسائر
            <small>ملخص الأداء المالي</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> لوحة التحكم</a></li>
            <li class="active">تقرير الأرباح والخسائر</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">

            {{-- البطاقات المالية --}}
            <div class="col-md-3">
                <div class="box box-success text-center">
                    <div class="box-header bg-success text-white fs-6 fw-bold rounded-top-4">
                        الإيرادات
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($revenues,2) }} ر.س
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="box box-danger text-center">
                    <div class="box-header bg-danger text-white fs-6 fw-bold rounded-top-4">
                        المصروفات
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($expenses,2) }} ر.س
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="box text-center">
                    <div class="box-header {{ $profit >= 0 ? 'bg-success' : 'bg-danger' }} text-white fs-6 fw-bold rounded-top-4">
                        الأرباح
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($profit,2) }} ر.س
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="box box-primary text-center">
                    <div class="box-header bg-primary text-white fs-6 fw-bold rounded-top-4">
                        رصيد الصندوق
                    </div>
                    <div class="box-body fs-5 fw-bold mt-2">
                        {{ number_format($cashBalance,2) }} ر.س
                    </div>
                </div>
            </div>

        </div>

        {{-- زر العودة --}}
        <div class="text-center mt-4">
            <a href="{{ route('dashboard.welcome') }}" class="btn btn-outline-secondary">⬅ العودة للرئيسية</a>
        </div>

    </section>

</div>

@endsection
