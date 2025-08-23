@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>الموردين</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li><a href="{{ route('dashboard.suppliers.index') }}">الموردين</a></li>
            <li class="active">إضافة مورد جديد</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">إضافة مورد جديد</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('dashboard.suppliers.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="name">اسم المورد</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>

                    <div class="form-group">
                        <label for="address">العنوان</label>
                        <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="balance">الرصيد الابتدائي</label>
                        <input type="number" name="balance" class="form-control" value="{{ old('balance', 0) }}" step="0.01">
                    </div>

                    <button type="submit" class="btn btn-primary">💾 حفظ المورد</button>
                </form>
            </div>
        </div>
    </section>

</div>
@endsection
