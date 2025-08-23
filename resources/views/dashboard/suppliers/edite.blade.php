@extends('layouts.dashboard.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <h1>الموردين</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.welcome') }}"><i class="fa fa-dashboard"></i> الرئيسية</a></li>
            <li><a href="{{ route('dashboard.suppliers.index') }}">الموردين</a></li>
            <li class="active">تعديل المورد</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">تعديل المورد</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('dashboard.suppliers.update', $supplier->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">اسم المورد</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                    </div>

                    <div class="form-group">
                        <label for="address">العنوان</label>
                        <textarea name="address" class="form-control">{{ old('address', $supplier->address) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="balance">الرصيد</label>
                        <input type="number" name="balance" class="form-control" value="{{ old('balance', $supplier->balance) }}" step="0.01">
                    </div>

                    <button type="submit" class="btn btn-primary">💾 تحديث المورد</button>
                </form>
            </div>
        </div>
    </section>

</div>
@endsection
